<?php

namespace App\Http\Middleware;

use App\Account;
use Closure;

class ConnectAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($account = Account::where('slug', $request->account)->first()) {
            setActiveAccount($account);

            return $next($request);
        }

        abort(404);
    }
}
