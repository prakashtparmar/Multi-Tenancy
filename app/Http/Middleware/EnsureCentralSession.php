<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EnsureCentralSession
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that central routes are not accessed with a tenant session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('tenant_id')) {
            Log::warning('Tenant session detected on central domain - clearing session', [
                'session_tenant' => $request->session()->get('tenant_id'),
                'user_id' => Auth::id(),
            ]);

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Session mismatch. Please login again.');
        }

        return $next($request);
    }
}
