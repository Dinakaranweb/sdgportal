<?php

use App\Http\Middleware\EnsureUserIsStaff;
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
        // Shared hosting puts a proxy in front of the application and ends the
        // secure connection there, so without this the portal believes it is
        // being reached over plain http and writes its stylesheet and script
        // addresses that way. A browser refuses to load those on a secure page,
        // which leaves the pages unstyled and the editor controls dead.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'staff' => EnsureUserIsStaff::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
