<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Throttle updates: Only update if last_seen_at is older than 2 minutes or null
            if (!$user->last_seen_at || $user->last_seen_at->lt(now()->subMinutes(2))) {
                $user->update([
                    'last_seen_at' => now(),
                ]);
            }
        }

        return $next($request);
    }
}
