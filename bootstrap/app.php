<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\VerificarEmpresa;
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
        $middleware->alias([
            'empresa.activa' => VerificarEmpresa::class,
            'role.redirect' => \App\Http\Middleware\RedirectByRole::class,
            'permiso' => CheckPermission::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\RedirectByRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
