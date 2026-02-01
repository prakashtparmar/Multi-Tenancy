<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EnsureTenantSession
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that sessions are properly scoped to tenants
     * and prevents session leakage between tenants.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (function_exists('tenant') && tenant()) {
            $tenantId = tenant('id');
            $sessionTenantId = $request->session()->get('tenant_id');

            // Debug Logging for Session Issues
            if (config('app.debug')) {
                Log::info('EnsureTenantSession Debug', [
                    'tenant_context' => $tenantId,
                    'session_tenant_id' => $sessionTenantId,
                    'auth_check' => Auth::check(),
                    'user_id' => Auth::id(),
                    'session_id' => $request->session()->getId(),
                    'domain' => $request->getHost(),
                ]);
            }

            // If no tenant ID in session, logic depends on auth status
            if (! $sessionTenantId) {
                if (Auth::check()) {
                    Log::warning('Authenticated user entered tenant context without tenant_id in session - potential leak', [
                        'user_id' => Auth::id(),
                        'tenant' => $tenantId
                    ]);

                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('tenant.login.view');
                }

                if (! app()->environment('testing')) {
                    $request->session()->put('tenant_id', $tenantId);
                }
            } elseif ($sessionTenantId !== $tenantId) {
                // Session belongs to different tenant - regenerate session for new tenant
                Log::warning('Session tenant mismatch detected - regenerating session', [
                    'session_tenant' => $sessionTenantId,
                    'current_tenant' => $tenantId,
                    'user_id' => Auth::id(),
                ]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->put('tenant_id', $tenantId);

                return redirect()->route('tenant.login.view');
            }
        }

        return $next($request);
    }
}
