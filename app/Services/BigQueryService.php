<?php namespace App\Services;


use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Core\ExponentialBackoff;

class BigQueryService {
    protected $BigQuery;

    public function __construct() {
        $this->BigQuery = new BigQueryClient([
            'projectId' => config('bigquery.project'),
            'keyFile' => config('bigquery.key'),
        ]);
    }

    public static function init() {
        return new static();
    }

    /**
     * Select from table.
     *
     * @param array $fields
     * @param string $suffix
     * @return \Google\Cloud\BigQuery\QueryResults
     */
    public function select(array $fields, string $suffix = '') {
        $query = 'SELECT ' . implode(', ', $fields) . ' ' .
            'FROM ' . config('bigquery.dataset') . '.' . config('bigquery.table') . ' ' .
            $suffix;

        // dd($query);

        return $this->BigQuery->runQuery(
            $this->BigQuery->query($query), [
                'returnRawResults' => true,
            ]
        );
    }
}
