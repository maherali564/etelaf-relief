<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('filament.admin.auth.login');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, __('admin.unauthorized'));
        }

        return $next($request);
    }
}
