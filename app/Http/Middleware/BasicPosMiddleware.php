<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicPosMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');

        if (!$authorization || !str_starts_with($authorization, 'Basic ')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Missing or invalid Authorization header.'
            ], 401);
        }

        $encodedCredentials = trim(substr($authorization, 6));
        $decoded = base64_decode($encodedCredentials);

        if (!$decoded || !str_contains($decoded, ':')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token format.'
            ], 401);
        }

        [$username, $password] = explode(':', $decoded, 2);

        $validUsername = env('POS_USER');
        $validPassword = env('POS_PASS');

        if ($username !== $validUsername || $password !== $validPassword) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid credentials.'
            ], 401);
        }

        return $next($request);
    }
}
