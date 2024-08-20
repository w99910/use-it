<?php

namespace ThomasBrillion\UseIt;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use ThomasBrillion\UseIt\Http\Middlewares\CanUseFeatureMiddleware;

class UseItServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if ($this->app->make('config')->get('use-it.routes', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'use-it-migrations');

        $this->publishes([
            __DIR__ . '/../config/use-it.php' => $this->app->configPath('use-it.php'),
        ], 'use-it');

        // Register middleware
        $router = $this->app->make('router');
        if ($router && method_exists($router, 'aliasMiddleware')) {
            $router->aliasMiddleware('can-use-feature', CanUseFeatureMiddleware::class);
        }
    }
}
