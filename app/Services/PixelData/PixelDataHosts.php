<?php namespace App\Services\PixelData;

class PixelDataHosts extends PixelDataAbstract {
    public function getHosts() {
        $this->checkRequiredAttributes([
            'Tracker'
        ]);

        $query = <<<SQL
            SELECT DISTINCT host
            FROM :table
            WHERE {$this->generateWhereString(array_merge([
                ['id', '=', '@pixel_id'],
            ], $this->getDateWheres()))}
            ORDER BY host
        SQL;

        return $this->runRawQuery($query, [
            'pixel_id' => $this->Tracker->pixel_id,
        ]);
    }
}