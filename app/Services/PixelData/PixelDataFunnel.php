<?php namespace App\Services\PixelData;

use Illuminate\Support\Collection;

class PixelDataFunnel extends PixelDataAbstract {
    protected $previous_steps;
    protected $filters = [];

    public function setPreviousSteps(array|Collection $previous_steps) {
        $this->previous_steps = is_array($previous_steps) ? collect($previous_steps) : $previous_steps;
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
            'pixel_id', 'start_date', 'end_date', 'previous_steps',
        ]);

        $uids_query = $this->getUidsQuery($this->previous_steps);

        $query = <<<SQL
            WITH uidz as ( $uids_query )
            SELECT paths as step_completed, COUNT(*) as users,
                SUM(COUNT(*)) OVER (ORDER BY paths DESC) AS total_users
            FROM uidz
            GROUP BY paths
            ORDER BY paths
        SQL;

        // dd($query);

        $results = $this->runRawQuery($query);

        $step_users = [];
        foreach ($results as $row) {
            $step_users[$row['step_completed']] = $row['total_users'];
            // dump($row);
        }

        // dump($step_users);

        $page_counts = [];
        $views_next = null;
        foreach ($this->previous_steps as $idx => $_step) {
            // $views = $step_users[$idx +1] ?? ( $step_users[$idx +2] ?? 0 );

            $views = $views_next ?: $this->getNextFromArray($step_users, ($idx + 1));
            $views_next = $idx+1 === count($this->previous_steps) ? null : $this->getNextFromArray($step_users, ($idx + 2));

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
                'label' => $_step['label'],
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
}
