<?php namespace App\Services\PixelData;

class PixelDataUniquePageviews extends PixelDataAbstract {
    protected $previous_pages;
    protected $search;
    protected $filters = [];

    public function setHost(string $host) {
        $this->setFilters([
            'host' => $host,
        ]);

        return $this;
    }

    public function setPreviousPages(array $previous_pages) {
        $this->previous_pages = $previous_pages;
        return $this;
    }

    public function setSearch(string $search) {
        $this->search = $search;
        return $this;
    }

    public function setFilters(array $filters) {
        $this->filters = array_filter(array_intersect_key($filters, $this::FILTERABLE_FIELDS));
        return $this;
    }

    public function getUniquePageviews() {
        $search_where = $this->search ? " AND path LIKE '%$this->search%' " : ' ';

        $filter_wheres = count($this->filters)
            ? ' AND ' . $this->generateWhereString($this->filters) . ' '
            : ' ';

        if ( ! count($this->previous_pages)) {
            $query = ('
                SELECT host_path, count(distinct uid) as views
                FROM :table
                WHERE DATE(ts) >= "' . $this->start_date->toDateString() . '"' .
                    ' AND DATE(ts) <= "' . $this->end_date->toDateString() . '"' .
                    ' AND id = @pixel_id' .
                    ' AND ( ev = "pageload" OR ev = "pageview" )' .
                    ' AND host_path IS NOT null' .
                    $search_where . $filter_wheres .
                ' GROUP BY host_path ORDER BY views desc, host_path'
            );
        } else {
            $uids_query = $this->getUidsQuery($this->previous_pages, $this->start_date, $this->end_date);
            $page_count = count($this->previous_pages);
            $this->previous_pages_string = json_encode($this->previous_pages, JSON_UNESCAPED_SLASHES);

            $query = <<<SQL
                DECLARE pages_all ARRAY<STRING(255)>;
                SET pages_all = $this->previous_pages_string;

                WITH uidz as ( $uids_query )
                SELECT evNext.host_path, COUNT(DISTINCT evNext.uid) as views
                FROM :table evNext
                    JOIN uidz
                        ON uidz.paths = $page_count
                            AND uidz.uid = evNext.uid
                            AND evNext.ts > uidz.end_ts
                WHERE evNext.host_path NOT IN UNNEST(pages_all)
                    AND evNext.host_path IS NOT null
                    AND evNext.id = @pixel_id
                    $search_where
                    $filter_wheres
                GROUP BY evNext.host_path
                ORDER BY views DESC, evNext.host_path
            SQL;
        }

        // dd($query);

        return $this->runRawQuery($query, [
            'pixel_id' => $this->Tracker->pixel_id,
        ]);
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
                $filter_wheres = count($this->filters)
                    ? 'AND ' . $this->generateWhereString($this->filters, "ev$idx")
                    : '';

                $paths_select_string .= ' + ';

                $prev_idx = $idx-1;
                $joins_string .= "
                    FULL OUTER JOIN :table ev$idx
                        ON ev$idx.uid = ev0.uid
                            AND ev$idx.id = @pixel_id
                            AND ev$idx.ts > ev$prev_idx.ts
                            AND date(ev$idx.ts) <= '$end_string'
                            AND ( ev$idx.ev = 'pageload' OR ev$idx.ev = 'pageview' )
                            AND ev$idx.host_path = '$_page'
                            $filter_wheres
                ";
            }

            $paths_select_string .= "IF(ev$idx.host_path IS NOT NULL, 1, 0)";
        }

        $filter_wheres = count($this->filters)
            ? 'AND ' . $this->generateWhereString($this->filters, "ev0")
            : '';

        $query = <<<SQL
            SELECT uid, MAX(paths) as paths,
                MIN(last_ts) as end_ts
            FROM (
                SELECT ev0.uid as uid, ev$last_page_idx.ts as last_ts,
                    ( $paths_select_string ) AS paths
                FROM :table ev0
                    $joins_string

                WHERE ev0.id = @pixel_id
                    AND date(ev0.ts) >= '$start_string'
                    AND date(ev0.ts) <= '$end_string'
                    AND ( ev0.ev = 'pageload' OR ev0.ev = 'pageview' )
                    AND ev0.host_path = '$first_page'
                    $filter_wheres
                ) inz
            GROUP BY uid
        SQL;

        return $query;
    }
}
