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

    public function getTableRef() {
        return config('bigquery.dataset') . '.' . config('bigquery.table');
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
            'FROM ' . $this->getTableRef() . ' ' .
            $suffix;

        return $this->BigQuery->runQuery(
            $this->BigQuery->query($query), [
                'returnRawResults' => true,
            ]
        );
    }


    /**
     * Run a raw query.
     *
     * Include :table for references to the "main" analytics table.
     *
     * @param string $query
     * @return \Google\Cloud\BigQuery\QueryResults
     */
    public function rawQuery($query, array $parameters = []) {
        $query = str_replace(':table', $this->getTableRef(), $query);

        return $this->BigQuery->runQuery(
            $this->BigQuery->query($query)->parameters($parameters),
            [
                'returnRawResults' => true,
            ]
        );
    }
}
