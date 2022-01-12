<?php namespace App\Services\PixelData;

class PixelDataHosts extends PixelDataAbstract {
    public function getHosts() {
        $this->checkRequiredAttributes([
            'pixel_id'
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
            'pixel_id' => $this->pixel_id,
        ]);
    }
}
