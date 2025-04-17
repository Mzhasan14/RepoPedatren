<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Jika pengguna tidak terautentikasi, kita bisa mengembalikan respon 403
        if (! $request->user() || ! $request->user()->hasRole($role)) {
            return response()->json([
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses resource ini.'
            ], 403);
        }

        return $next($request);
    }
}
