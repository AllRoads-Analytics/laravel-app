<?php namespace App\Services\PixelData;

class PixelDataTotalPageviews extends PixelDataAbstract {
    public function getPageviewCount() {
        $this->checkRequiredAttributes([
            'pixel_id'
        ]);

        $query = <<<SQL
            SELECT count(*) pageviews
            FROM :table
            WHERE {$this->generateWhereString(array_merge([
                ['id', '=', $this->pixel_id],
            ], $this->getDateWheres()))}
                AND (ev = 'pageload' OR ev = 'pageview')
        SQL;

        // dd($query);

        return (integer) $this->runRawQuery($query)->current()['pageviews'];
    }
}
