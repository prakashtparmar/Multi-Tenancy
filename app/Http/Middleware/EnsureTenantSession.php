<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        // Only apply to tenant contexts
        if (tenancy()->initialized) {
            $tenantId = tenant('id');

            // Get current session tenant ID
            $sessionTenantId = $request->session()->get('tenant_id');

            \Log::info('EnsureTenantSession: Checking session', [
                'url' => $request->fullUrl(),
                'tenant_context' => $tenantId,
                'session_tenant_id' => $sessionTenantId,
                'user_id' => auth()->id(),
                'auth_check' => auth()->check() ? 'true' : 'false',
            ]);

            // If no tenant ID in session, logic depends on auth status
            if (! $sessionTenantId) {
                // If the user is already authenticated but lacks a tenant_id in the session,
                // it indicates a session leakage from the central domain or another context.
                // We must invalidate this session to prevent unauthorized cross-context access.
                if (auth()->check()) {
                   \Log::warning('Authenticated user entered tenant context without tenant_id in session - potential leak', [
                        'user_id' => auth()->id(),
                        'tenant' => $tenantId
                   ]);
                   
                   auth()->logout();
                   $request->session()->invalidate();
                   $request->session()->regenerateToken();
                   
                   return redirect()->route('tenant.login.view')
                        ->with('error', 'Session invalid. Please login again.');
                }

                // If guest, it's safe to start a new tenant session
                $request->session()->put('tenant_id', $tenantId);
                $sessionTenantId = $tenantId;
            }

            // Verify session belongs to current tenant
            if ($sessionTenantId !== $tenantId) {
                // Session belongs to different tenant - regenerate session for new tenant
                \Log::warning('Session tenant mismatch detected - regenerating session', [
                    'session_tenant' => $sessionTenantId,
                    'current_tenant' => $tenantId,
                    'user_id' => auth()->id(),
                ]);

                // Logout from old tenant
                auth()->logout();

                // Regenerate session for new tenant
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->put('tenant_id', $tenantId);

                return redirect()->route('tenant.login.view')
                    ->with('error', 'Session expired. Please login again.');
            }
        }

        return $next($request);
    }
}
