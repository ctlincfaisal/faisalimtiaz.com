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

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your marketing session has expired. Refresh the page and sign in again before sending.'], 401);
        }

        return redirect()->guest(route('marketing.login'));
    }
}
