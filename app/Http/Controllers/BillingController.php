<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invite;
use App\Models\Organization;
use App\Services\Plan;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    // =========================================================================
    // Plan selection.
    // =========================================================================

    public function get_select_plan(Request $Request, Organization $Organization) {
        return view('_pages.billing.plan', [
            'Organization' => $Organization,
            'Plan' => $Organization->getPlan(),
            'plans' => config('billing.plans'),
            'allowed_plan_ids' => $Organization->getAllowedPlans()
                ->pluck('id')->toArray(),
        ]);
    }

    public function post_select_plan(Request $Request, Organization $Organization) {
        $Request->validate([
            'plan' => Rule::in(Plan::allIds()),
        ]);

        $Plan = Plan::getById($Request->input('plan'));

        // If not already subscribed...
        if ( ! $Organization->subscribed('default')) {
            //  ...and "free" not selected, and DOES NOT have payment method, send to payment page.
            if ( ! $Plan->isFree() && ! $Organization->hasDefaultPaymentMethod()) {
                $Request->session()->flash('alert', [
                    'type' => 'warning',
                    'message' => "Add a payment method, to complete subscription.",
                ]);

                return redirect()->action(
                    [ $this::class, 'get_update_payment_method' ],
                    [
                        'organization' => $Organization->id,
                        'plan' => $Plan->id,
                    ],
                );
            }
            // ...and "free" selected, or already has a payment method, just redirect to Org.
            else {
                $Request->session()->flash('alert', [
                    'type' => 'success',
                    'message' => "Plan set to \"$Plan->label\".",
                ]);

                return redirect()->route('organizations.show', $Organization->id);
            }
        }

        // If already subscribed...
        else {
            // ... and "free" selected, cancel subscription.
            if ($Plan->isFree()) {
                $Organization->subscription('default')->cancel();

                $Request->session()->flash('alert', [
                    'type' => 'success',
                    'message' => 'When plan expires, account will be switched to "Free" plan.',
                ]);

                return redirect()->route('organizations.show', $Organization->id);
            }

            // .. and "free" not selected, swap plan.
            else {
                if ( ! $Organization->subscribedToPrice($Plan->monthly_price_stripe_id, 'default')) {
                    $Organization->subscription('default')->swap($Plan->monthly_price_stripe_id);

                    $Request->session()->flash('alert', [
                        'type' => 'success',
                        'message' => "Plan updated to \"$Plan->label\".",
                    ]);

                    return redirect()->route('organizations.show', $Organization->id);

                } else {
                    $Request->session()->flash('alert', [
                        'type' => 'warning',
                        'message' => "Plan already set to \"$Plan->label\".",
                    ]);

                    return redirect()->route('organizations.show', $Organization->id);
                }
            }
        }

        throw new \Exception("Unexpected state.");
    }


    // =========================================================================
    // Payment Method.
    // =========================================================================

    public function get_update_payment_method(Request $Request, Organization $Organization) {
        return view('_pages.billing.payment', [
            'intent' => $Organization->createSetupIntent(),
            'Plan' => $Request->has('plan') ? Plan::getById($Request->input('plan')) : null,
            'Organization' => $Organization,
        ]);
    }

    public function post_update_payment_method(Request $Request, Organization $Organization) {
        $Request->validate([
            'name' => 'required|string|min:3|max:255',
            'stripe_payment_method' => 'required',
            'new_plan' => [
                'nullable',
                Rule::in(Plan::allIds()),
            ],
        ]);

        $stripe_payment_method = $Request->input('stripe_payment_method');
        $Plan = $Request->has('plan') ? Plan::getById($Request->input('plan')) : null;
        $already_subscribed = $Organization->subscribed('default');

        // dump($Request->all());

        $stripe_customer_fields = [
            'name' => "$Organization->name",
            'email' => auth()->user()->email,
        ];

        // Update or create Stripe customer.
        if ( ! $Organization->hasStripeId()) {
            $Organization->createAsStripeCustomer($stripe_customer_fields);
        } else {
            $Organization->updateStripeCustomer($stripe_customer_fields);
        }

        // If org is already subscribed, or not subscribing, we're just updating their payment method.
        if ($already_subscribed || ! $Plan) {
            $Organization->updateDefaultPaymentMethod($stripe_payment_method);

            $message = 'Payment method successfully replaced.';
        }

        // (else) Plan was selected, and org is not subscribed, so we'll subscribe them to the selected plan.
        else {
            $Organization->newSubscription('default', $Plan->monthly_price_stripe_id)
                ->create($stripe_payment_method);

            $message = "Successfully subscribed to \"$Plan->label\" plan.";
        }

        $Request->session()->flash('alert', [
            'type' => 'success',
            'message' => $message,
        ]);

        return redirect()->route('organizations.show', $Organization->id);
    }
}
