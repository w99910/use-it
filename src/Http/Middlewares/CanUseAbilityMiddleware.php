<?php

namespace ThomasBrillion\UseIt\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CanUseAbilityMiddleware
{
    public function handle(Request $request, Closure $next, string $feature, string $guard = null): Response
    {
        $user = $request->user($guard);
        if (!$user) {
            return new Response('User not found in request. Please login or authenticate yourself', 401);
        }

        if (!method_exists($user, 'canUseFeature')) {
            return new Response('Please Add \ThomasBrillion\UseIt\Traits\CanUseIt Trait in your model', 404);
        }

        if (!$user->canUseFeature($feature, $request->input('amount'))) {
            return new Response("Sorry. You cannot use $feature Feature", 401);
        }

        return $next($request);
    }
}
