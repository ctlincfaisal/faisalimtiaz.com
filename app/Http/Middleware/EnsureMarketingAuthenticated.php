<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMarketingAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('marketing_authenticated') === true) {
            return $next($request);
        }

        return redirect()->guest(route('marketing.login'));
    }
}
