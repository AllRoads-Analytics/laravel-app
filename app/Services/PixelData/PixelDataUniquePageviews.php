<?php namespace App\Services\PixelData;

class PixelDataUniquePageviews extends PixelDataAbstract {
    protected $host;
    protected $previous_pages;

    public function setHost(string $host) {
        $this->host = $host;
        return $this;
    }

    public function setPreviousPages(array $previous_pages) {
        $this->previous_pages = $previous_pages;
        return $this;
    }

    public function getUniquePageviews() {
        if ( ! count($this->previous_pages)) {
            $query = ('
                SELECT path, count(distinct uid) as views
                FROM :table
                WHERE DATE(ts) >= "' . $this->start_date->toDateString() . '"' .
                    ' AND DATE(ts) <= "' . $this->end_date->toDateString() . '"' .
                    ' AND id = "' . $this->Tracker->pixel_id . '"' .
                    ' AND host = "' . $this->host . '"' .
                    ' AND ev = "pageload"' .
                ' GROUP BY path ORDER BY views desc, path'
            );
        } else {
            $uids_query = $this->getUidsQuery($this->previous_pages, $this->start_date, $this->end_date);
            $page_count = count($this->previous_pages);
            $this->previous_pages_string = json_encode($this->previous_pages, JSON_UNESCAPED_SLASHES);

            $query = <<<SQL
                DECLARE pages_all ARRAY<STRING(255)>;
                SET pages_all = $this->previous_pages_string;

                WITH uidz as ( $uids_query )
                SELECT evNext.path, COUNT(DISTINCT evNext.uid) as views
                FROM pixel_events.events evNext
                    JOIN uidz
                        ON uidz.paths = $page_count
                            AND uidz.uid = evNext.uid
                            AND evNext.ts > uidz.end_ts
                WHERE evNext.path NOT IN UNNEST(pages_all)
                GROUP BY evNext.path
                ORDER BY views DESC, evNext.path
            SQL;
        }

        return $this->runRawQuery($query);
    }


    // =========================================================================
    // Protected functions.
    // =========================================================================

    protected function getUidsQuery() {
        $start_string = $this->start_date->toDateString();
        $end_string = $this->end_date->toDateString();

        $first_page = $this->previous_pages[0];
        $last_page_idx = count($this->previous_pages) -1;

        $paths_select_string = '';
        $joins_string = '';
        foreach ($this->previous_pages as $idx => $_page) {
            if ($idx !== 0) {
                $paths_select_string .= ' + ';

                $prev_idx = $idx-1;
                $joins_string .= "
                    FULL OUTER JOIN pixel_events.events ev$idx
                        ON ev$idx.uid = ev0.uid
                            AND ev$idx.ts > ev$prev_idx.ts
                            AND date(ev$idx.ts) <= '$end_string'
                            AND ev$idx.host = '$this->host'
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
                    AND ev0.host = '$this->host'
                    AND ev0.ev = 'pageload'
                    AND ev0.path = '$first_page'
                ) inz
            GROUP BY uid
        SQL;

        return $query;
    }
}
