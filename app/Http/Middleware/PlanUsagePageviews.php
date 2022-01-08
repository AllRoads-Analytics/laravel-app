<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;

class PlanUsagePageviews
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $Request, Closure $next)
    {
        $Organization = $Request->organization ?: $Request->tracker->Organization;

        if ($Organization->getPlanUsage()->limitReached('limit_pageviews_per_month')) {
            $Request->session()->flash('alert', [
                'type' => 'warning',
                'message' => "Your organization has exceeded its <b>monthly pageview limit</b>.
                    <br>
                    Please upgrade to a plan with a higher limit.
                ",
            ]);

            return redirect()->route('organizations.billing.get_select_plan', $Organization->id);
        }

        return $next($Request);
    }
}
