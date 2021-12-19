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
            $uids_query = $this->getUidsQuery($previous_pages, $start_date, $end_date);
            $page_count = count($previous_pages);
            $pages_string = json_encode($previous_pages, JSON_UNESCAPED_SLASHES);

            $query = <<<SQL
                DECLARE pages_all ARRAY<STRING(255)>;
                SET pages_all = $pages_string;

                WITH uidz as ( $uids_query )
                SELECT evNext.path, COUNT(DISTINCT evNext.uid) as views
                FROM pixel_events.events evNext
                    JOIN uidz
                        ON uidz.paths = $page_count
                            AND uidz.uid = evNext.uid
                            AND evNext.ts > uidz.end_ts
                WHERE evNext.path NOT IN UNNEST(pages_all)
                GROUP BY evNext.path
                ORDER BY views DESC
            SQL;

            $rows = App::make(BigQueryService::class)->rawQuery($query, [
                'host' => $host,
            ]);
        }

        // foreach ($rows as $row) {
        //     dump($row);
        // }
        // die;


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
    public function getFunnelViews(string $host, Carbon $start_date, Carbon $end_date, array $pages) {
        $uids_query = $this->getUidsQuery($pages, $start_date, $end_date);

        $query = <<<SQL
            WITH uidz as ( $uids_query )
            SELECT paths as step_completed, COUNT(*) as users,
                SUM(COUNT(*)) OVER (ORDER BY paths DESC) AS total_users
            FROM uidz
            GROUP BY paths
            ORDER BY paths
        SQL;

        $results = App::make(BigQueryService::class)->rawQuery($query);


        $step_users = [];
        foreach ($results as $row) {
            $step_users[$row['step_completed']] = $row['total_users'];
            // dump($row);
        }

        // dump($step_users);

        $page_counts = [];
        $prev_views = 1;
        foreach ($pages as $idx => $page) {
            $views = $step_users[$idx +1] ?? $prev_views;

            $page_counts[] = [
                'page' => $page,
                'views' => $views,
            ];

            $prev_views = $views;
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
                $string .= "('$page' = path_1) ";
            } else {
                $prev_page = $pages[$i -1];
                $string .= "OR ('$page' = path_1 AND '$prev_page' = path_2) ";
            }
        }

        $string .= ')';
        return $string;
    }

    protected function getUidsQuery(array $pages, Carbon $start_date, Carbon $end_date) {
        $start_string = $start_date->toDateString();
        $end_string = $end_date->toDateString();

        $first_page = $pages[0];
        $last_page_idx = count($pages) -1;

        $paths_select_string = '';
        $joins_string = '';
        foreach ($pages as $idx => $_page) {
            if ($idx !== 0) {
                $paths_select_string .= ' + ';

                $prev_idx = $idx-1;
                $joins_string .= "
                    FULL OUTER JOIN pixel_events.events ev$idx
                        ON ev$idx.uid = ev0.uid
                            AND ev$idx.ts > ev$prev_idx.ts
                            AND date(ev$idx.ts) <= '$end_string'
                            AND ev$idx.host = 'www.timcieplowski.com'
                            AND ev$idx.ev = 'pageload'
                            AND ev$idx.path = '$_page'
                ";
            }

            $paths_select_string .= "IF(ev$idx.path IS NOT NULL, 1, 0)";
        }

        $query = <<<SQL
            SELECT uid, MAX(paths) as paths,
                MIN(last_ts) as end_ts
            FROM (
                SELECT ev0.uid as uid, ev$last_page_idx.ts as last_ts,
                    ( $paths_select_string ) AS paths
                FROM pixel_events.events ev0
                    $joins_string

                WHERE date(ev0.ts) > '$start_string'
                    AND date(ev0.ts) <= '$end_string'
                    AND ev0.host = 'www.timcieplowski.com'
                    AND ev0.ev = 'pageload'
                    AND ev0.path = '$first_page'
                ) inz
            GROUP BY uid
        SQL;

        return $query;
    }
}
