<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;

class GlobalAdmin
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
        $User = $Request->user();

        if ($User && $User->flag_admin) {
            return $next($Request);
        } else {
            return abort(403);
        }
    }
}
