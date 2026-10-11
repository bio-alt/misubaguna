<?php

namespace App\Providers;

use App\Ejma\Data\MaterialRepository;
use App\Ejma\Data\Tooling;
use App\Ejma\DesignSearch;
use App\Ejma\EjmaCalculator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MaterialRepository::class, fn ($app) => new MaterialRepository(
            $app['config']->get('ejma.materials', [])
        ));
        $this->app->singleton(EjmaCalculator::class, fn ($app) => new EjmaCalculator(
            $app->make(MaterialRepository::class)
        ));
        $this->app->singleton(Tooling::class, fn ($app) => new Tooling(
            $app['config']->get('ejma.tooling', [])
        ));
        $this->app->singleton(DesignSearch::class, fn ($app) => new DesignSearch(
            $app->make(EjmaCalculator::class),
            $app->make(Tooling::class),
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
