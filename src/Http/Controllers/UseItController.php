<?php

namespace ThomasBrillion\UseIt\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use ThomasBrillion\UseIt\Services\FeatureService;

class UseItController
{
    public static function routes(): void
    {
        Route::group(['prefix' => 'use-it', 'middleware' => ['auth']], function () {
            Route::post('/can/{feature}', [static::class, 'canUseFeature']);
            Route::post('/try/{feature}', [static::class, 'tryFeature']);
        });
    }

    public function canUseFeature(Request $request, string $featureName): Response
    {
        try {
            $user = $request->user();
            $canUse = (new FeatureService($user))->canUse($featureName, $request->get('amount'));

            return new Response($canUse);
        } catch (\Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    public function tryFeature(Request $request, string $featureName): Response
    {
        try {
            $user = $request->user();
            $consumption = (new FeatureService($user))->try(
                $featureName,
                $request->get('amount'),
                $request->get('meta')
            );

            // convert to array
            if (method_exists($consumption, 'toArray')) {
                $consumption = $consumption->toArray();
            }

            return new Response($consumption);
        } catch (\Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode() ?: 500);
        }

    }
}
