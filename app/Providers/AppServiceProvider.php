<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->singleton(\App\Services\FilterPesertaDidikService::class);
        $this->app->singleton(\App\Services\FilterPelajarService::class);
        $this->app->singleton(\App\Services\FilterSantriService::class);
        $this->app->singleton(\App\Services\FilterKhadamService::class);
        $this->app->singleton(\App\Services\FilterAlumniService::class);
        $this->app->singleton(\App\Services\FilterBersaudaraService::class);
        $this->app->singleton(\App\Services\FilterPerizinanService::class);


        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
