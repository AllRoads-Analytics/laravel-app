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
    // Getters.
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
        return $this->Organization->Users()->count() + $this->Organization->Invites()->count();
    }

    public function get_usage_limit_pageviews_per_month() {
        return (integer) cache()->remember(
            "usage_limit_pageviews_per_month_{$this->Organization->id}",
            1800, // 1800 sec = 30 min
            function() {
                return PixelDataTotalPageviews::init()
                    ->setPixelId($this->Organization->pixel_id)
                    ->setDateRange(now()->setDay(1), now())
                    ->getPageviewCount();
            }
        );
    }


    // =========================================================================
    // Public instance functions.
    // =========================================================================

    public function limitReached($key) {
        if ( ! in_array($key, array_keys(self::LIMITS))) {
            throw new \Exception("Plan limit [$key] does not exist.");
        }

        return $this->{"get_usage_$key"}() >= $this->Plan->$key;
    }

    public function anyLimitReached() {
        foreach (self::LIMITS as $key => $value) {
            if ($this->limitReached($key)) {
                return true;
            }
        }

        return false;
    }
}
