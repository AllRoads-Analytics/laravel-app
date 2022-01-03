<?php namespace App\Services;

use App\Models\Organization;
use App\Services\PixelData\PixelDataTotalPageviews;

class PlanUsage {
    const LIMITS = [
        'limit_users' => 'Users',
        'limit_funnels' => 'Funnels',
        'limit_pageviews_per_month' => 'Pageviews per month',
        // 'limit_data_view_days' => 'Data retention (days)',
    ];

    /** @var Organization */
    protected $Organization;

    /** @var Plan */
    protected $Plan;


    // =========================================================================
    // Constructors.
    // =========================================================================

    public function __construct(Organization $Organization, Plan $Plan) {
        $this->Organization = $Organization;
        $this->Plan = $Plan;
    }

    public static function init(Organization $Organization, Plan $Plan) {
        return new self($Organization, $Plan);
    }


    // =========================================================================
    // Public instance functions.
    // =========================================================================

    public function getAll() {
        $array = [];

        foreach (self::LIMITS as $key => $label) {
            $limit = $this->Plan->{$key};
            $usage = $this->{"get_usage_$key"}();

            $array[$key] = [
                'key' => $key,
                'label' => $label,
                'limit' => $limit,
                'usage' => $usage,
                'percentage' => is_integer($usage) ? round($usage / $limit * 100) : null,
            ];
        }

        // dd($array);

        return $array;
    }

    public function get_usage_limit_funnels() {
        return $this->Organization->Funnels()->count();
    }

    public function get_usage_limit_users() {
        return $this->Organization->Users()->count();
    }

    public function get_usage_limit_pageviews_per_month() {
        return (integer) cache()->remember(
            "usage_limit_pageviews_per_month_{$this->Organization->id}",
            300, // 300 sec = 5 min
            function() {
                return PixelDataTotalPageviews::init()
                    ->setTracker($this->Organization->getTracker())
                    ->setDateRange(now()->setDay(1), now())
                    ->getPageviewCount();
            }
        );
    }
}
