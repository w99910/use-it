<?php

namespace ThomasBrillion\UseIt\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanUseAnyFeatureMiddleware
{
    public function handle(Request $request, Closure $next, ...$features): Response
    {
        $user = $request->user();
        if (!$user) {
            return new Response('Please authenticate yourself.', 401);
        }

        if (!method_exists($user, 'canUseAnyFeature')) {
            return new Response('Please Add \ThomasBrillion\UseIt\Traits\CanUseIt Trait in your model', 404);
        }

        if (!$user->canUseAnyFeature($features, $request->input('amount'))) {
            return new Response("Sorry. You are not allowed to proceed.", 403);
        }

        return $next($request);
    }
}
