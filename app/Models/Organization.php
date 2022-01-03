<?php

namespace App\Models;

use App\Services\Plan;
use App\Models\ModelAbstract;
use App\Services\PaymentCard;
use App\Services\PlanUsage;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Subscription;

class Organization extends ModelAbstract
{
    use HasFactory;
    use Billable;

    /** @var Plan */
    protected $Plan;

    protected static function booted() {
        static::created(function($Organization) {
            Tracker::create([
                'organization_id' => $Organization->id,
                'pixel_id' => 'ID-' . uniqid(),
            ]);
        });
    }

    // =========================================================================
    // Getters.
    // =========================================================================

    /**
     * @return Tracker
     */
    public function getTracker() {
        return $this->hasMany(Tracker::class)->first();
    }

    /**
     * @return PaymentCard|null
     */
    public function getPaymentCard() {
        return $this->hasDefaultPaymentMethod()
            ? PaymentCard::init($this->defaultPaymentMethod()->toArray()['card'])
            : null;
    }

    /**
     * @return Plan
     */
    public function getPlan() {
        if (isset($this->Plan)) {
            return $this->Plan;
        }

        if ($this->subscribed('default')) {
            /** @var Subscription */
            $Subscription = $this->subscription('default');

            return Plan::getByStripePriceId($Subscription->stripe_price);
        }

        return $this->Plan = Plan::getById('free');
    }

    /** @var PlanUsage */
    public function getPlanUsage() {
        return PlanUsage::init($this, $this->getPlan());
    }

    // =========================================================================
    // Relations.
    // =========================================================================

    public function Users() {
        return $this->belongsToMany(User::class);
    }

    public function Invites() {
        return $this->hasMany(Invite::class);
    }

    public function Funnels() {
        return $this->hasMany(Funnel::class);
    }


    // =========================================================================
    // Public instance functions.
    // =========================================================================

    public function addUser(User $User, string $role) {
        if ( ! in_array($role, User::ROLES)) {
            throw new \Exception("Role [$role] not legit.");
        }

        $this->Users()->attach($User->id, [
            'role' => $role,
        ]);
        return $this;
    }

    // public function newOrUpdateSubscription
}
