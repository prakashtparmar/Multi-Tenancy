<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralSession
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that central routes are not accessed with a tenant session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if session has tenant_id but we are on central domain
        \Log::info('EnsureCentralSession: Checking session', [
            'url' => $request->fullUrl(),
            'has_tenant_id' => $request->session()->has('tenant_id'),
            'session_tenant' => $request->session()->get('tenant_id'),
            'user_id' => auth()->id(),
            'is_tenant_helper' => tenant() ? 'yes' : 'no'
        ]);

        if ($request->session()->has('tenant_id')) {
            \Log::warning('Tenant session detected on central domain - clearing session', [
                'session_tenant' => $request->session()->get('tenant_id'),
                'user_id' => auth()->id(),
            ]);

            // Logout to prevent "logged in as tenant user on central"
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Session mismatch. Please login again.');
        }

        return $next($request);
    }
}
