<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class IsSuperAdmin
{
    /**
     * Only the owner account (user_type = admin) may pass; staff accounts
     * are rejected even though they pass the regular "admin" middleware.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->user_type == 'admin') {
            return $next($request);
        }
        else{
            abort(403);
        }
    }
}
