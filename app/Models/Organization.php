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
        static::creating(function($Organization) {
            $Organization->pixel_id = 'ID-' . uniqid();
        });
    }

    // =========================================================================
    // Getters.
    // =========================================================================

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

    /**
     * @return PlanUsage
     */
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

    public function getExploreRoute() {
        return route('pathfinder.tracker', $this->pixel_id);
    }

    public function getSettingsRoute() {
        return route('organizations.show', $this->id);
    }

    public function addUser(User $User, string $role) {
        if ( ! in_array($role, User::ROLES)) {
            throw new \Exception("Role [$role] not legit.");
        }

        $this->Users()->attach($User->id, [
            'role' => $role,
        ]);
        return $this;
    }

    public function getAllowedPlans() {
        $usage = $this->getPlanUsage()->getAll();

        return Plan::all()->reduce( function($plans, $Plan) use ($usage) {
            foreach (['limit_users', 'limit_funnels'] as $key) {
                if (null !== $Plan->$key && $usage[$key]['usage'] > $Plan->$key) {
                    return $plans;
                }
            }

            return $plans->push($Plan);
        }, collect());
    }

    public function getCodeSnippet() {
        $code =
<<<JS
<!-- Start AllRoads Snippet -->
<script>
! function(e, t, n, a, p, r, s) {
e[a] || ((p = e[a] = function() {
p.process ? p.process.apply(p, arguments) : p.queue.push(arguments)
}).queue = [], p.t = + new Date, (r = t.createElement(n)).async = 1, r.src = "https://cdn.allroadsanalytics.com/allroads.min.js", (s = t.getElementsByTagName(n)[0]).parentNode.insertBefore(r, s))
}(window, document, "script", "allroads"),
allroads("init", "$this->pixel_id", {follow: true}),
allroads("event", "pageload");
</script>
<!-- End AllRoads Snippet -->
JS;

        return $code;
    }
}
