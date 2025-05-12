<?php

namespace App\Providers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

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
        Activity::saving(function (Activity $activity) {
            $activity->properties = $activity->properties->merge([
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::fullUrl(),
            ]);
        });
    }
}
