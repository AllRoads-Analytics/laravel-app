<?php namespace App\Services\PixelData;

use App\Models\Funnel;
use Illuminate\Support\Collection;

class PixelDataUniquePageviews extends PixelDataAbstract {
    protected $previous_steps;
    protected $search;
    protected $host;
    protected $filters = [];

    public function setHost(string $host) {
        $this->host = $host;
        return $this;
    }

    public function setPreviousSteps(array|Collection $previous_steps) {
        $this->previous_steps = is_array($previous_steps) ? collect($previous_steps) : $previous_steps;
        return $this;
    }

    public function setSearch(string $search) {
        $this->search = $search;
        return $this;
    }

    public function setFilters(array $filters) {
        $this->filters = array_filter(array_intersect_key($filters, $this::FILTERABLE_FIELDS));
        return $this;
    }

    public function getUniquePageviews() {
        $this->checkRequiredAttributes([
            'pixel_id'
        ]);

        $host_placeholder = isset($this->host) ? $this->addQueryParameter($this->host) : null;
        $search_placeholder = $this->search ? $this->addQueryParameter("%$this->search%") : null;

        if ( ! count($this->previous_steps)) {
            $host_where = $host_placeholder ? " AND host = @$host_placeholder " : ' ';
            $search_where = $search_placeholder ? " AND path LIKE @$search_placeholder " : ' ';

            $filter_wheres = count($this->filters)
                ? ' AND ' . $this->generateWhereString($this->filters) . ' '
                : ' ';

            $query = ('
                SELECT host_path, count(distinct uid) as views
                FROM :table
                WHERE DATE(ts) >= @start_string' .
                    ' AND DATE(ts) <= @end_string' .
                    ' AND id = @pixel_id' .
                    ' AND ( ev = "pageload" OR ev = "pageview" )' .
                    ' AND host_path IS NOT null' .
                    $host_where . $search_where . $filter_wheres .
                ' GROUP BY host_path ORDER BY views desc, host_path'
            );

            $this->addQueryParameters([
                'pixel_id' => $this->pixel_id,
                'start_string' => $this->start_date->toDateString(),
                'end_string' => $this->end_date->toDateString(),
            ]);

        } else {
            $uids_query = $this->getUidsQuery($this->previous_steps);

            $step_count = count($this->previous_steps);

            $host_where = $host_placeholder ? " AND evNext.host = @$host_placeholder " : ' ';
            $search_where = $search_placeholder ? " AND evNext.path LIKE @$search_placeholder " : ' ';

            $previous_pages_string = json_encode(
                $this->previous_steps->where('type', Funnel::STEP_TYPE_PAGELOAD_HOST_PATH)->pluck('match_data'),
                JSON_UNESCAPED_SLASHES
            );

            $filter_wheres = count($this->filters)
                ? ' AND ' . $this->generateWhereString($this->filters, 'evNext') . ' '
                : ' ';

            $query = <<<SQL
                DECLARE pages_all ARRAY<STRING(255)>;
                SET pages_all = $previous_pages_string;

                WITH uidz as ( $uids_query )
                SELECT evNext.host_path, COUNT(DISTINCT evNext.uid) as views
                FROM :table evNext
                    JOIN uidz
                        ON uidz.paths = $step_count
                            AND uidz.uid = evNext.uid
                            AND evNext.ts > uidz.end_ts
                WHERE evNext.host_path NOT IN UNNEST(pages_all)
                    AND evNext.host_path IS NOT null
                    AND evNext.id = @pixel_id
                    $host_where
                    $search_where
                    $filter_wheres
                GROUP BY evNext.host_path
                ORDER BY views DESC, evNext.host_path
            SQL;
        }

        $this->addQueryParameters([
            'pixel_id' => $this->pixel_id,
        ]);

        return $this->runRawQuery($query);
    }
}
