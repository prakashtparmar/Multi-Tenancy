<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
        // Only apply to authenticated users in initialized tenant context
        if (! Auth::check() || ! tenancy()->initialized) {
            return $next($request);
        }

        // Enforce Tenant Status
        if (tenant('status') !== 'active') {
            Auth::logout();

            return redirect($request->getSchemeAndHttpHost() . '/login')
                ->with('error', 'This workspace is currently ' . tenant('status') . '. Please contact support.');
        }

        $user = Auth::user();

        // Verify user exists in current tenant database to prevent cross-tenant leakage
        $userExists = User::where('id', $user->id)
            ->where('email', $user->email)
            ->exists();

        if (! $userExists) {
            Log::error('User attempted to access unauthorized tenant', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'tenant_id' => tenant('id'),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect($request->getSchemeAndHttpHost() . '/login')
                ->with('error', 'Access denied. You do not have access to this workspace.');
        }

        return $next($request);
    }
}
