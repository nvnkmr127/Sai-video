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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectTo(guests: '/admin/login');
        $middleware->alias([
            'is_admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        $trustedProxies = env('TRUSTED_PROXIES');
        if (is_string($trustedProxies) && $trustedProxies !== '') {
            $at = trim($trustedProxies) === '*'
                ? '*'
                : array_values(array_filter(array_map('trim', explode(',', $trustedProxies))));
            $middleware->trustProxies($at);
        }
        // Rate limiting
        $middleware->throttleApi(); // or use default throttle
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('webhooks:retry-failed')->everyThirtyMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
