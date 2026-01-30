<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTenantAccess
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures users can only access their own tenant's data
     * and prevents cross-tenant data access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users in tenant context
        // Skip if user is not authenticated (prevents issues during login)
        if (! auth()->check() || ! tenancy()->initialized) {
            return $next($request);
        }

        // Enforce Tenant Status
        // Enforce Tenant Status
        if (tenant('status') !== 'active') {
            auth()->logout();

            // Explicitly redirect to the current domain's login to ensure we don't drift to central
            // and preserve flash messages by NOT invalidating session here (just logout)
            return redirect($request->getSchemeAndHttpHost() . '/login')
                ->with('error', 'This workspace is currently '.tenant('status').'. Please contact support.');
        }

        $user = auth()->user();
        $tenantId = tenant('id');

        // Verify user exists in current tenant database
        $userExists = \App\Models\User::where('id', $user->id)
            ->where('email', $user->email)
            ->exists();

        if (! $userExists) {
            \Log::error('User attempted to access tenant they do not belong to', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'tenant_id' => $tenantId,
            ]);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('tenant.login.view')
                ->with('error', 'Access denied. You do not have access to this workspace.');
        }

        return $next($request);
    }
}
