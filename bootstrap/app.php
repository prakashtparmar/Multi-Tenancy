<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware) {
        $middleware->alias([
            'tenant.session' => \App\Http\Middleware\EnsureTenantSession::class,
            'tenant.access' => \App\Http\Middleware\ValidateTenantAccess::class,
            'central.session' => \App\Http\Middleware\EnsureCentralSession::class,
            'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->redirectGuestsTo(function (Illuminate\Http\Request $request) {
            $isTenant = tenant() ? 'yes (' . tenant('id') . ')' : 'no';
            \Log::info("Redirecting guest. URL: " . $request->fullUrl() . ", Tenant Detected: $isTenant, Route: " . $request->route()?->getName());
            
            if (tenant()) {
                return route('tenant.login.view');
            }
            
            // Fallback: If we are on a subdomain (e.g. foo.localhost) but no tenant detected yet,
            // we should try to redirect to that domain's login page rather than central
            $host = $request->getHost();
            if ($host !== 'localhost' && $host !== '127.0.0.1' && !str_contains($host, 'central')) {
                 \Log::info("Tenant not detected via helper, but host '$host' implies tenant. Redirecting to host login.");
                 // Manually construct the login URL for the current host
                 // Use SchemeAndHttpHost to preserve http/https and port
                 return $request->getSchemeAndHttpHost() . '/login';
            }

            // Final Fallback: Use the current request domain dynamically
            return $request->getSchemeAndHttpHost() . '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException $e, $request) {
            return response()->view('errors.tenant-not-found', [], 404);
        });
    })->create();
