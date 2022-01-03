<?php namespace App\Services;

/**
 * @property-read string $id
 * @property-read string $label
 * @property-read integer $monthly_price
 * @property-read integer $limit_funnels
 * @property-read integer $limit_data_view_days
 * @property-read integer $limit_users
 * @property-read integer $limit_pageviews_per_month
 * @property-read string $monthly_price_stripe_id
 */
class Plan {
    protected $config;

    public function __get($name) {
        if (in_array($name, array_keys($this->config))) {
            return $this->config[$name];
        }

        return null;
    }

    // =========================================================================
    // Constructors.
    // =========================================================================

    public function __construct(string $id) {
        if ( ! in_array($id, self::allIds())) {
            throw new \Exception("Invalid plan ID [$id].");
        }

        $this->config = config("billing.plans.$id");
    }

    public static function getById(string $id) {
        return new self($id);
    }

    public static function getByStripePriceId(string $stripe_price_id) {
        foreach (config('billing.plans') as $_plan) {
            if ($stripe_price_id === $_plan['monthly_price_stripe_id']) {
                return new self($_plan['id']);
            }
        }

        return null;
    }


    // =========================================================================
    // Public static functions.
    // =========================================================================

    public static function allIds() {
        return array_keys(config('billing.plans'));
    }


    // =========================================================================
    // Public instance functions.
    // =========================================================================

    public function isFree() {
        return $this->monthly_price <= 0;
    }
}
