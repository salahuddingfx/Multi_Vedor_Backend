<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            RateLimiter::for('api', function (object $job) {
                return Limit::perMinute(300)->by($job->ip() ?? 'unknown');
            });

            RateLimiter::for('admin', function (object $job) {
                return Limit::perMinute(500)->by($job->ip() ?? 'unknown');
            });

            RateLimiter::for('login', function (object $job) {
                return Limit::perMinute(5)->by($job->ip() ?? 'unknown');
            });

            RateLimiter::for('orders', function (object $job) {
                return Limit::perMinute(10)->by($job->ip() ?? 'unknown');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'working_hours' => \App\Http\Middleware\CheckWorkingHours::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
