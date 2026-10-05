<?php

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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
