<?php

namespace App\Providers;

use App\Models\UserOrtu;
use Illuminate\Auth\Notifications\ResetPassword;
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

        ResetPassword::createUrlUsing(function ($user, string $token) {
            $frontendUrl = env('FRONTEND_URL');

            if ($user instanceof UserOrtu) {
                return $frontendUrl . '/reset-password-ortu?token=' . $token . '&email=' . urlencode($user->email);
            }

            return $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });
    }
}
