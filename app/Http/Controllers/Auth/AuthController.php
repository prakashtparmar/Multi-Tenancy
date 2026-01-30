<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): mixed
    {
        if (config('app.debug')) {
            \Log::debug('Login attempt', ['email' => $request->email, 'tenant' => tenant('id')]);
        }

        // Force 'remember' to false for Auth::attempt to ensure session expiration on close.
        // We handle 'remember email' manually below.
        if (! Auth::attempt($request->only('email', 'password'), false)) {
            \Log::warning('Login failed for '.$request->email);
            if ($request->expectsJson()) {
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            return back()->withErrors(['email' => __('auth.failed')]);
        }

        if (config('app.debug')) {
            \Log::debug('Login successful for '.$request->email);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Store tenant ID in session for security middleware
        // Note: Auth::attempt() already regenerates the session internally
        if (tenant()) {
            $request->session()->put('tenant_id', tenant('id'));
            \Log::info('Stored tenant_id in session', ['tenant_id' => tenant('id')]);
        }

        // Log activity
        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->log('User logged in to tenant: '.tenant('id'));

        // Generate Sanctum token for API support
        $token = $user->createToken('auth_token')->plainTextToken;

        // Secure "Remember Username" Logic (Email Persistence Only)
        // Note: We only persist the email, never the password for security reasons
        if ($request->boolean('remember')) {
            // Save email for 30 days
            \Illuminate\Support\Facades\Cookie::queue('saved_email', $request->email, 43200);
        } else {
            // Forget email if unchecked
            \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('saved_email'));
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

        // Redirect based on context
        if (tenant()) {
            return redirect(request()->getSchemeAndHttpHost().'/dashboard')
                ->with('success', 'Welcome back!');
        }

        return redirect(config('app.url').'/dashboard')
            ->with('success', 'Welcome back!');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): mixed
    {
        $user = $request->user();

        if ($user) {
            // Revoke all tokens
            $user->tokens()->delete();

            activity()
                ->performedOn($user)
                ->causedBy($user)
                ->log('User logged out from tenant: '.tenant('id'));
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logged out successfully',
            ]);
        }

        if (tenant()) {
            return redirect(request()->getSchemeAndHttpHost().'/login');
        }

        return redirect(config('app.url').'/login');
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
