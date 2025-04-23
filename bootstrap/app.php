<?php

use App\Http\Middleware\FilamentUnauthorizedRedirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('universal', [
            InitializeTenancyByDomain::class,
            InitializeTenancyBySubdomain::class,
        ]);

        $middleware->alias([
            // 'filament.unauthorized.redirect' => FilamentUnauthorizedRedirect::class,
        ]);
        // $middleware->trustProxies(at:
        // '*'
    //  );

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
