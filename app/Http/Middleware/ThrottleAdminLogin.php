<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAdminLogin
{
    public function handle(Request $request, Closure $next, string $maxAttempts = '5', string $decayMinutes = '1'): Response
    {
        if (! $request->is('admin/login') && ! $request->is('admin/livewire*/login')) {
            return $next($request);
        }

        $key = 'admin-login:'.($request->ip() ?: 'unknown');

        $limiter = app(RateLimiter::class);

        if ($limiter->tooManyAttempts($key, (int) $maxAttempts)) {
            return response()->view('errors.429', [], 429);
        }

        $response = $next($request);

        if (! $response->isSuccessful()) {
            $limiter->hit($key, (int) $decayMinutes * 60);
        }

        return $response;
    }
}
