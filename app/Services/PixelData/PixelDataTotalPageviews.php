<?php namespace App\Services\PixelData;

class PixelDataTotalPageviews extends PixelDataAbstract {
    public function getPageviewCount() {
        $this->checkRequiredAttributes([
            'Tracker'
        ]);

        $query = <<<SQL
            SELECT count(*) pageviews
            FROM :table
            WHERE {$this->generateWhereString(array_merge([
                ['id', '=', '@pixel_id'],
            ], $this->getDateWheres()))}
                AND (ev = 'pageload' OR ev = 'pageview')
        SQL;

        // dd($query);

        return (integer) $this->runRawQuery($query, [
            'pixel_id' => $this->Tracker->pixel_id,
        ])->current()['pageviews'];
    }
}
