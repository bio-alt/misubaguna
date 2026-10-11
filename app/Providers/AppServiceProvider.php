<?php

namespace App\Providers;

use App\Ejma\Data\MaterialRepository;
use App\Ejma\EjmaCalculator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EjmaCalculator::class, fn ($app) => new EjmaCalculator(
            new MaterialRepository($app['config']->get('ejma.materials', []))
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
