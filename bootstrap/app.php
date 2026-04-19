<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureEmailVerificationIfEnabled;
use App\Http\Middleware\EnsureNotBanned;
use App\Http\Middleware\EnsureRegistrationEnabled;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'not_banned' => EnsureNotBanned::class,
            'verify_if_enabled' => EnsureEmailVerificationIfEnabled::class,
            'registration_enabled' => EnsureRegistrationEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
