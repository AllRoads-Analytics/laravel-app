<?php namespace App\Services\PixelData;

class PixelDataFilterOptions extends PixelDataAbstract {
    /**
     * [
     *      [
     *          'key' => 'md',
     *          'label' => 'Mobile Device',
     *          'options' => [
     *             'true', 'false'
     *          ]
     *      ],
     *      ...
     * ]
     *
     * @return array
     */
    public function getFilterOptions() {
        $this->checkRequiredAttributes([
            'Tracker', 'start_date', 'end_date',
        ]);

        $filters_string = implode(', ', array_keys($this::FILTERABLE_FIELDS));

        $select_distincts_array = implode(', ', array_map( function($field) {
            return <<<SQL
                ( ARRAY( SELECT DISTINCT $field FROM pageviews WHERE $field IS NOT NULL ) ) $field
            SQL;
        }, array_keys($this::FILTERABLE_FIELDS)));

        $start_string = $this->start_date->toDateString();
        $end_string = $this->end_date->toDateString();

        $query = <<<SQL
            WITH pageviews AS (
                SELECT $filters_string FROM :table as ev0
                WHERE date(ev0.ts) >= '$start_string'
                    AND date(ev0.ts) <= '$end_string'
                    AND ev0.id = @pixel_id
                    AND ( ev0.ev = 'pageload' OR ev0.ev = 'pageview' )
            )

            SELECT $select_distincts_array
        SQL;

        // dd($query);

        $results = $this->runRawQuery($query, [
            'pixel_id' => $this->Tracker->pixel_id
        ]);

        $filter_options = [];
        foreach ($results->current() as $_field => $_values) {
            $filter_options[] = [
                'key' => $_field,
                'label' => $this::FILTERABLE_FIELDS[$_field],
                'options' => array_filter(array_map( function ($_value) {
                    return $_value['v'];
                }, $_values)),
            ];
        }

       return $filter_options;
    }
}
