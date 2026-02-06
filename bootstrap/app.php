<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureTenantSession;
use App\Http\Middleware\ValidateTenantAccess;
use App\Http\Middleware\EnsureCentralSession;
use App\Http\Middleware\PreventBackHistory;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant.session' => EnsureTenantSession::class,
            'tenant.access' => ValidateTenantAccess::class,
            'central.session' => EnsureCentralSession::class,
            'prevent-back-history' => PreventBackHistory::class,
        ]);

        $middleware->appendToGroup('web', [
            PreventBackHistory::class,
            \App\Http\Middleware\TrackUserActivity::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if (tenant()) {
                return route('tenant.login.view');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TenantCouldNotBeIdentifiedOnDomainException $e, Request $request) {
            return response()->view('errors.tenant-not-found', [], 404);
        });
    })->create();
