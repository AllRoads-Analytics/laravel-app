<?php

namespace App\Models;

use Carbon\Carbon;
use App\Services\BigQueryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\App;

class Tracker extends ModelAbstract
{
    use HasFactory;

    // =========================================================================
    // Finders.
    // =========================================================================

    /**
     * @param string $pixel_id
     * @return static
     */
    public static function findByPixelId($pixel_id) {
        return static::where('pixel_id', $pixel_id)->first();
    }


    // =========================================================================
    // Public instance functions.
    // =========================================================================

    public function getRoute() {
        return route('pathfinder.tracker', $this->pixel_id);
    }

    public function getCodeSnippet() {
        $code = '<!-- Start Pathfinder Snippet -->
<script>
!function(e,t,n,a,p,r,s){e[a]||((p=e[a]=function(){p.process?p.process.apply(p,arguments):p.queue.push(arguments)}).queue=[],p.t=+new Date,(r=t.createElement(n)).async=1,r.src="https://probable-skill-330219.ue.r.appspot.com/pathfinder.min.js?t="+864e5*Math.ceil(new Date/864e5),(s=t.getElementsByTagName(n)[0]).parentNode.insertBefore(r,s))}(window,document,"script","pathfinder"),pathfinder("init","' . $this->pixel_id . '"),pathfinder("event","pageload");
</script>
<!-- End Pathfinder Snippet -->';

        return $code;
    }


    // =========================================================================
    // Relations.
    // =========================================================================

    public function Organization() {
        return $this->belongsTo(Organization::class);
    }


    // =========================================================================
    // Queries.
    // =========================================================================

    public function getHosts() {
        $rows = App::make(BigQueryService::class)->select(
            ['distinct host',],
            'WHERE id = "' . $this->pixel_id . '"'
        )->rows();

        return $rows;
    }

    public function getUniquePageviews(string $host, Carbon $start_date, Carbon $end_date, array $previous_pages = []) {
        if ( ! count($previous_pages)) {
            $rows = App::make(BigQueryService::class)->select(
                ['path', 'count(distinct uid) as views'],
                'WHERE DATE(ts) >= "' . $start_date->toDateString() . '"' .
                    ' AND DATE(ts) <= "' . $end_date->toDateString() . '"' .
                    ' AND id = "' . $this->pixel_id . '"' .
                    ' AND host = "' . $host . '"' .
                    ' AND ev = "pageload"' .
                ' GROUP BY path ORDER BY views desc'
            )->rows();
        } else {
            $start_string = $start_date->toDateString();
            $end_string = $end_date->toDateString();

            $pages_string = json_encode($previous_pages, JSON_UNESCAPED_SLASHES);
            $pages_where_string = $this->getPagesWhereString($previous_pages);
            $pages_count = count($previous_pages);

            $query = <<<SQL
                DECLARE pages_all ARRAY<STRING(255)>;
                SET pages_all = $pages_string;

                WITH history AS (
                    SELECT ev1.uid, ev1.path as path_1,
                        ev2.path as path_2
                    FROM :table ev1

                    FULL OUTER JOIN :table ev2
                        ON ev2.uid = ev1.uid
                        AND DATE(ev2.ts) > '$start_string'
                        AND DATE(ev1.ts) <= '$end_string'
                        AND ev2.host = @host
                        AND ev2.ev = 'pageload'
                        AND ev2.ts < ev1.ts
                        AND ev2.path != ev1.path
                        AND (
                            (ev2.path is null)
                            OR
                            ( ev2.path in UNNEST(pages_all) )
                        )
                    WHERE DATE(ev1.ts) > '$start_string'
                        AND DATE(ev1.ts) <= '$end_string'
                        AND ev1.host = @host
                        AND ev1.ev = 'pageload'
                    GROUP BY uid, path_1, path_2
                )

                SELECT path_1 as path, COUNT(DISTINCT uid) views
                FROM history
                WHERE uid IN (
                    SELECT uid
                    FROM history
                    WHERE $pages_where_string
                    GROUP BY uid
                    HAVING COUNT(*) = $pages_count
                )
                AND path_1 NOT IN UNNEST(pages_all)
                GROUP BY path_1
                ORDER BY views DESC;
            SQL;

            $rows = App::make(BigQueryService::class)->rawQuery($query, [
                'host' => $host,
            ]);
        }


        return $rows;
    }

    /**
     * [
     *      [
     *          'page' => 'foo',
     *          'views' => 99
     *      ],
     *      ...
     * ]
     *
     * @param string $host
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param array $pages
     * @return \Google\Cloud\BigQuery\QueryResults
     */
    public function getFunnelViews2(string $host, Carbon $start_date, Carbon $end_date, array $pages) {
        $start_string = $start_date->toDateString();
        $end_string = $end_date->toDateString();

        $pages_string = json_encode($pages, JSON_UNESCAPED_SLASHES);
        $pages_where_string = $this->getPagesWhereString($pages);

        $results = App::make(BigQueryService::class)->rawQuery(<<<SQL
            DECLARE pages_all ARRAY<STRING(255)>;
            SET pages_all = $pages_string;

            WITH history AS (
                SELECT ev1.uid, ev1.path as path_1,
                    ev2.path as path_2
                FROM :table ev1

                FULL OUTER JOIN :table ev2
                    ON ev2.uid = ev1.uid
                    AND DATE(ev2.ts) > '$start_string'
                    AND DATE(ev1.ts) <= '$end_string'
                    AND ev2.host = @host
                    AND ev2.ev = 'pageload'
                    AND ev2.ts < ev1.ts
                    AND ev2.path != ev1.path
                    AND (
                        (ev2.path is null)
                        OR
                        ( ev2.path in UNNEST(pages_all) )
                    )
                WHERE DATE(ev1.ts) > '$start_string'
                    AND DATE(ev1.ts) <= '$end_string'
                    AND ev1.host = @host
                    AND ev1.ev = 'pageload'
                    AND ev1.path in UNNEST(pages_all)
                GROUP BY uid, path_1, path_2
            )

            SELECT step_completed, COUNT(uid) AS users,
                SUM(COUNT(uid)) OVER (ORDER BY step_completed DESC) AS total_users
            FROM (
                SELECT uid, count(*) step_completed
                FROM history
                WHERE $pages_where_string
                GROUP BY uid
            )
            GROUP BY step_completed
            ORDER BY step_completed
        SQL, [
            'host' => $host,
        ]);

        $step_users = [];
        foreach ($results as $row) {
            $step_users[$row['step_completed']] = $row['total_users'];
        }

        $page_counts = [];
        foreach ($pages as $idx => $page) {
            $page_counts[] = [
                'page' => $page,
                'views' => $step_users[$idx +1] ?? 1,
            ];
        }

        return $page_counts;
    }


    // =========================================================================
    // Protected functions.
    // =========================================================================

    protected function getPagesWhereString($pages) {
        $string = '(';

        foreach ($pages as $i => $page) {
            if (0 === $i) {
                $string .= "('$page' = path_1 AND path_2 IS NULL) ";
            } else {
                $prev_page = $pages[$i -1];
                $string .= "OR ('$page' = path_1 AND '$prev_page' = path_2) ";
            }
        }

        $string .= ')';
        return $string;
    }
}
