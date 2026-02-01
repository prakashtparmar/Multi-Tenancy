<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): mixed
    {
        if (config('app.debug')) {
            Log::debug('Login attempt', ['email' => $request->email, 'tenant' => tenant('id')]);
        }

        // Force 'remember' to false for Auth::attempt to ensure session expiration on close.
        // We handle 'remember email' manually via cookie persistence.
        if (! Auth::attempt($request->only('email', 'password'), false)) {
            Log::warning('Login failed for ' . $request->email);
            
            if ($request->expectsJson()) {
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            return back()->withInput()->withErrors(['email' => __('auth.failed')]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (config('app.debug')) {
            Log::debug('Login successful for ' . $request->email);
        }

        // Store tenant ID in session for security middleware
        if (tenant()) {
            $request->session()->put('tenant_id', tenant('id'));
        }

        // Log activity
        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->log('User logged in to tenant: ' . (tenant('id') ?? 'central'));

        // Generate Sanctum token for API support
        $token = $user->createToken('auth_token')->plainTextToken;

        // Secure "Remember Username" Logic (Email Persistence Only)
        if ($request->boolean('remember')) {
            // Save email for 30 days
            Cookie::queue('saved_email', (string) $request->email, 43200);
        } else {
            // Forget email if unchecked
            Cookie::queue(Cookie::forget('saved_email'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login successful',
                'user' => $user->load('roles'),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'tenant' => tenant('id'),
            ]);
        }

        return redirect()->route(tenant() ? 'tenant.dashboard' : 'dashboard')
            ->with('success', 'Welcome back!');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): mixed
    {
        /** @var User $user */
        $user = $request->user();

        if ($user) {
            // Revoke all tokens
            $user->tokens()->delete();

            activity()
                ->performedOn($user)
                ->causedBy($user)
                ->log('User logged out');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logged out successfully',
            ]);
        }

        $loginUrl = tenant() ? '/login' : '/login'; // Dynamic based on host
        
        return redirect(request()->getSchemeAndHttpHost() . $loginUrl);
    }

    /**
     * Get the authenticated User.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('roles', 'permissions'),
            'tenant' => tenant('id'),
        ]);
    }
}
