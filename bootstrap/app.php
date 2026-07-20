<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. Mendaftarkan middleware bawaan web & Inertia
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        // 2. Perbaikan Utama: Mempercayai proxy Cloudflare agar me-forward HTTPS dengan benar
        $middleware->trustProxies(at: '*');

        // 3. Registrasi alias middleware kustom
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
