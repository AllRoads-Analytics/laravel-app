<?php namespace App\Services\PixelData;

use App\Models\Funnel;
use Carbon\Carbon;
use App\Services\BigQueryService;
use Illuminate\Support\Facades\App;

class PixelDataAbstract {
    const FILTERABLE_FIELDS = [
        'md' => 'Mobile Device?',
        'bn' => 'Browser',
        'utm_source' => 'Source (utm_source)',
        'utm_medium' => 'Medium (utm_medium)',
        'utm_term' => 'Term (utm_term)',
        'utm_content' => 'Content (utm_content)',
        'utm_campaign' => 'Name (utm_campaign)',
        'host' => 'Hostname',
    ];

    /** @var array */
    protected $parameters = [];

    protected $parameter_idx = 0;

    /** @var string */
    protected $pixel_id;

    /** @var Carbon */
    protected $start_date;
    /** @var Carbon */
    protected $end_date;

    protected $limit;
    protected $offset;

    public static function init() {
        return new static();
    }

    // =========================================================================
    // Setters.
    // =========================================================================

    public function setPixelId(string $pixel_id) {
        $this->pixel_id = $pixel_id;
        return $this;
    }

    public function setDateRange(Carbon $start, Carbon $end) {
        $this->start_date = $start;
        $this->end_date = $end;
        return $this;
    }

    public function setLimit(int $limit) {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset(int $offset) {
        $this->offset = $offset;
        return $this;
    }


    // =========================================================================
    // Protected functions.
    // =========================================================================

    protected function runRawQuery(string $query, array $parameters = []) {
        $query = str_replace(':table', $this->getTableRef(), $query);

        if (isset($this->limit)) {
            $query .= " LIMIT $this->limit";
        }

        if (isset($this->offset)) {
            $query .= " OFFSET $this->offset";
        }

        // dd([$query, $this->parameters]);

        return $this->getBigQueryService()->rawQuery($query, $this->parameters)->rows();
    }

    protected function getTableRef() {
        return config('bigquery.dataset') . '.' . config('bigquery.table');
    }

    /**
     * @return BigQueryService
     */
    protected function getBigQueryService() {
        return App::make(BigQueryService::class);
    }

    protected function hasDates() {
        return isset($this->start_date) || isset($this->end_date);
    }

    protected function getDateWheres() {
        $wheres = [];

        if (isset($this->start_date)) {
            $wheres[] = ['DATE(ts)', '>=', $this->start_date->toDateString()];
        }

        if (isset($this->end_date)) {
            $wheres[] = ['DATE(ts)', '<=', $this->end_date->toDateString()];
        }

        return $wheres;
    }

    protected function generateWhereString(array $wheres, string $table_prefix = '') {
        $string = '';
        $table_prefix = $table_prefix ? "$table_prefix." : '';

        $i = 0;
        foreach ($wheres as $key => $value) {
            if (0 !== $i) {
                $string .= ' AND ';
            }

            if ( ! is_array($value)) {
                $value = [ $key, '=', $value ];
            }

            $value_prime = $value[2];

            if ($value_prime === 'true' || $value_prime === 'false') {
                $value_prime = $value_prime === 'true' ? true : false;
            }

            $placeholder = $this->addQueryParameter($value_prime);

            $string .= "{$table_prefix}{$value[0]} {$value[1]} @$placeholder";

            $i++;
        }

        return $string;
    }

    protected function checkRequiredAttributes(array $attributes) {
        foreach ($attributes as $attribute) {
            if ( ! isset($this->{$attribute})) {
                throw new \Exception("Attribute [$attribute] must be set on [" . $this::class . '] .');
            }
        }
    }

    protected function getUidsQuery($previous_steps) {
        $first_step_where = $this->getStepWhere($previous_steps[0]);

        $last_step_idx = count($previous_steps) -1;

        $paths_select_string = '';
        $joins_string = '';
        foreach ($this->previous_steps as $idx => $_step) {
            if ($idx !== 0) {
                $filter_wheres = count($this->filters)
                    ? 'AND ' . $this->generateWhereString($this->filters, "ev$idx")
                    : '';

                $paths_select_string .= ' + ';

                $step_where = $this->getStepWhere($_step);

                $prev_idx = $idx-1;
                $joins_string .= "
                    FULL OUTER JOIN :table ev$idx
                        ON ev$idx.uid = ev0.uid
                            AND ev$idx.id = @pixel_id
                            AND ev$idx.ts > ev$prev_idx.ts
                            AND date(ev$idx.ts) <= @end_string
                            AND ( ev$idx.ev = 'pageload' OR ev$idx.ev = 'pageview' )
                            AND ev$idx.$step_where
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
                SELECT ev0.uid as uid, ev$last_step_idx.ts as last_ts,
                    ( $paths_select_string ) AS paths
                FROM :table ev0
                    $joins_string

                WHERE ev0.id = @pixel_id
                    AND date(ev0.ts) >= @start_string
                    AND date(ev0.ts) <= @end_string
                    AND ( ev0.ev = 'pageload' OR ev0.ev = 'pageview' )
                    AND ev0.$first_step_where
                    $filter_wheres
                ) inz
            GROUP BY uid
        SQL;

        $this->addQueryParameters([
            'pixel_id' => $this->pixel_id,
            'start_string' => $this->start_date->toDateString(),
            'end_string' => $this->end_date->toDateString(),
        ]);

        return $query;
    }

    protected function getStepWhere($step) {
        if ( ! (isset($step['type']) && isset($step['match_data']))) {
            throw new \Exception("Invalid step format.");
        }

        $key = $this->addQueryParameter($step['match_data']);

        switch ($step['type']) {
            case Funnel::STEP_TYPE_PAGELOAD_HOST_PATH:
                return "host_path = @$key ";
            case Funnel::STEP_TYPE_PAGELOAD_HOST_PATH_LIKE:
                return "host_path LIKE @$key ";
        }

        throw new \Exception("Invalid step type: " . $step['type']);
    }

    protected function addQueryParameter($value, $key = null) {
        $key = $key ?: 'param';

        $key = $key . '_' . $this->parameter_idx;

        $this->parameters[$key] = $value;

        $this->parameter_idx++;

        return $key;
    }

    protected function addQueryParameters($new_params) {
        $this->parameters = array_merge($this->parameters, $new_params);
        return $this;
    }
}
