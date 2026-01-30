<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// Note: Middleware is already applied in TenancyServiceProvider->mapRoutes()
// No need to wrap routes again

// Auth Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('tenant.login.view');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('tenant.login');

// Protected Routes - with tenant security
Route::middleware(['auth', 'tenant.session', 'tenant.access'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('tenant.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('tenant.me');

    // Tenant Dashboard & UI
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('tenant.dashboard');

    Route::get('/settings', function () {
        return view('settings');
    })->name('tenant.settings');

    // Identity Management
    Route::post('users/bulk', [\App\Http\Controllers\Platform\UserController::class, 'bulkAction'])->name('tenant.users.bulk');
    Route::post('users/{id}/restore', [\App\Http\Controllers\Platform\UserController::class, 'restore'])->name('tenant.users.restore');
    Route::resource('users', \App\Http\Controllers\Platform\UserController::class)->names('tenant.users');
    Route::resource('roles', \App\Http\Controllers\Platform\RoleController::class)->names('tenant.roles');
    Route::resource('permissions', \App\Http\Controllers\Platform\PermissionController::class)->names('tenant.permissions');

    // CRM
    Route::post('customers/bulk', [\App\Http\Controllers\Tenant\CustomerController::class, 'bulk'])->name('tenant.customers.bulk');
    Route::post('customers/{id}/restore', [\App\Http\Controllers\Tenant\CustomerController::class, 'restore'])->name('tenant.customers.restore');
    Route::resource('customers', \App\Http\Controllers\Tenant\CustomerController::class)->names('tenant.customers');

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\Platform\ActivityLogController::class, 'index'])->name('tenant.activity-logs.index');
});

// Debug Route
Route::get('/debug-url', function () {
    return [
        'tenant_id' => tenant('id'),
        'login_route' => route('tenant.login'),
        'app_url' => config('app.url'),
        'request_host' => request()->getHost(),
    ];
});

// Root redirect
Route::get('/', function () {
    // Explicitly use the current host for redirects to avoid any central/tenant confusion
    $protocol = request()->secure() ? 'https://' : 'http://';
    $host = request()->getHost();
    $port = request()->getPort() ? ':'.request()->getPort() : '';
    $baseUrl = $protocol.$host.$port;

    if (! auth()->check()) {
        return redirect($baseUrl.'/login');
    }

    return redirect($baseUrl.'/dashboard');
});
