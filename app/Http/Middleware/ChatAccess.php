<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ChatAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        $user = Auth::user();
        if (! $user->can_chat && ! $user->isSuperAdmin()) {
            abort(403, 'Unauthorized. You do not have chat access.');
        }

        return $next($request);
    }
}
