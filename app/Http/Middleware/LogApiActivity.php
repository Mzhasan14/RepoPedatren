<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogApiActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Ambil user dari guard sanctum
        $user = $request->user();

        // Jangan log jika tidak ada user atau request OPTIONS
        if (!$user || $request->isMethod('OPTIONS')) {
            return $response;
        }

        activity('api')
            ->causedBy($user)
            ->withProperties([
                'method'   => $request->method(),
                'endpoint' => $request->path(),
                'ip'       => $request->ip(),
                'input'    => $request->except(['password', 'password_confirmation']),
                'status'   => $response->status(),
            ])
            ->log("API Request: {$request->method()} {$request->path()}");

        return $response;
    }
}
