<?php namespace App\Services\PixelData;

use Carbon\Carbon;
use App\Models\Tracker;
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
    ];

    /** @var Tracker */
    protected $Tracker;

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

    public function setTracker(Tracker $Tracker) {
        $this->Tracker = $Tracker;
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

        // dd($query);
        return $this->getBigQueryService()->rawQuery($query, $parameters)->rows();
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

            if (is_array($value)) {
                $string .= "{$table_prefix}{$value[0]} {$value[1]} "
                    . (
                        is_int($value[2]) || substr($value[2], 0, 1) === '@'
                        || 'true' === $value[2] || 'false' === $value[2]

                        ? $value[2] : "\"{$value[2]}\""
                    );
            } else {
                $string .= "{$table_prefix}{$key} = "
                    . (
                        is_int($value) || substr($value, 0, 1) === '@'
                        || 'true' === $value || 'false' === $value

                        ? $value : "\"{$value}\""
                    );
            }

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
}
