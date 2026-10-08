<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // These routes are a JSON API (see routes/web.php) and are called
        // without a CSRF token/session, so exempt them from CSRF checks.
        $middleware->validateCsrfTokens(except: [
            'products',
            'products/*',
            'customers',
            'customers/*',
            'tables',
            'tables/*',
            'menu-items',
            'menu-items/*',
            'reservations',
            'reservations/*',
            'orders',
            'orders/*',
            'order-items',
            'order-items/*',
            'auth/*',
            'users',
            'users/*',
        ]);

        // Lab 5: 'role:manager' / 'role:admin' — see app/Http/Middleware/EnsureRole.php
        $middleware->alias(['role' => EnsureRole::class]);

        // This is a JSON-only API with no "login" web route. Laravel's
        // default Authenticate::redirectTo() calls route('login') to build
        // a redirect target for guests, which throws RouteNotFoundException
        // (escaping before AuthenticationException is even constructed) the
        // moment a request doesn't send Accept: application/json. Disable
        // that redirect entirely — the render() callback below always
        // returns a clean JSON 401 instead.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // This is a JSON-only API with no "login" web route, so the default
        // Authenticate middleware's redirect-to-login fallback (triggered
        // whenever a request doesn't send Accept: application/json) would
        // otherwise blow up with a RouteNotFoundException → 500. Always
        // render a clean 401 JSON response instead, matching the Symfony
        // side's behavior for the same case.
        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json(['data' => ['error' => 'Unauthenticated']], 401);
        });
    })->create();
