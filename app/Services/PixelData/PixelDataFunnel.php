<?php namespace App\Services\PixelData;

class PixelDataFunnel extends PixelDataAbstract {
    protected $previous_pages;
    protected $filters = [];

    public function setPreviousPages(array $previous_pages) {
        $this->previous_pages = $previous_pages;
        return $this;
    }

    public function setFilters(array $filters) {
        $this->filters = array_intersect_key($filters, $this::FILTERABLE_FIELDS);
        return $this;
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
     * @return array
     */
    public function getFunnelViews() {
        $this->checkRequiredAttributes([
            'Tracker', 'start_date', 'end_date', 'previous_pages',
        ]);

        $uids_query = $this->getUidsQuery();

        $query = <<<SQL
            WITH uidz as ( $uids_query )
            SELECT paths as step_completed, COUNT(*) as users,
                SUM(COUNT(*)) OVER (ORDER BY paths DESC) AS total_users
            FROM uidz
            GROUP BY paths
            ORDER BY paths
        SQL;

        // dd($query);

        $results = $this->runRawQuery($query, [
            'pixel_id' => $this->Tracker->pixel_id,
        ]);

        $step_users = [];
        foreach ($results as $row) {
            $step_users[$row['step_completed']] = $row['total_users'];
            // dump($row);
        }

        // dump($step_users);

        $page_counts = [];
        $views_next = null;
        foreach ($this->previous_pages as $idx => $page) {
            // $views = $step_users[$idx +1] ?? ( $step_users[$idx +2] ?? 0 );

            $views = $views_next ?: $this->getNextFromArray($step_users, ($idx + 1));
            $views_next = $idx+1 === count($this->previous_pages) ? null : $this->getNextFromArray($step_users, ($idx + 2));

            if (count($page_counts)) {
                // If not the first page, and first page had views, calculate.
                // If not the first page, and first page had 0 views, 0%.
                $percentage = $page_counts[0]['views'] > 0
                    ? round($views / $page_counts[0]['views'] * 100) : 0;
            } else {
                // First page, 100&.
                $percentage = 100;
            }

            $page_counts[] = [
                'page' => $page,
                'views' => $views,
                'dropped' => is_null($views_next) ? null : $views - $views_next,
                'proceeded' => $views_next,
                'percentage' => $percentage,
                'step_dropped_percentage' => is_null($views_next) || !$views ?
                    null : round(($views - $views_next) / $views * 100),
                'step_proceeded_percentage' => is_null($views_next) || !$views ?
                    null : round($views_next / $views * 100),
            ];
        }

        return $page_counts;
    }


    // =========================================================================
    // Protected functions.
    // =========================================================================

    protected function getNextFromArray($array, $starting_index) {
        foreach ($array as $key => $value) {
            if ($key < $starting_index) continue;

            return $value;
        }

        return 0;
    }

    protected function getUidsQuery() {
        $start_string = $this->start_date->toDateString();
        $end_string = $this->end_date->toDateString();

        $first_page = $this->previous_pages[0];
        $last_page_idx = count($this->previous_pages) -1;

        $paths_select_string = '';
        $joins_string = '';
        foreach ($this->previous_pages as $idx => $_page) {
            $filter_wheres = count($this->filters)
                ? 'AND ' . $this->generateWhereString($this->filters, "ev$idx")
                : '';

            if ($idx !== 0) {
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

        // dd($query);

        return $query;
    }
}
