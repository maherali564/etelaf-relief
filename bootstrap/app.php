<?php

use App\Exceptions\DonationException;
use App\Exceptions\PaymentException;
use App\Exceptions\WebhookException;
use Illuminate\Validation\ValidationException;
use App\Http\Middleware\ChatAccess;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SecurityHeaders::class,
            SetLocale::class,
        ]);

        $middleware->alias([
            'chat-access' => ChatAccess::class,
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('*/donor/*') || $request->is('donor/*')
            ? route('donor.login', ['locale' => $request->segment(1) ?? app()->getLocale()])
            : route('filament.admin.auth.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (PaymentException $e) {
            //
        });

        $exceptions->reportable(function (WebhookException $e) {
            //
        });

        $exceptions->reportable(function (DonationException $e) {
            //
        });

        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('webhook/*')) {
                return response()->json([
                    'error' => 'validation_failed',
                    'messages' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('webhook/*')) {
                return response()->json(['error' => 'unauthenticated'], 401);
            }
        });
    })->create();
