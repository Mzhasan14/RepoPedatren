<?php

namespace App\Providers;

use App\Models\Santri;
use App\Models\Pelajar;
use App\Observers\SantriObserver;
use App\Observers\PelajarObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
