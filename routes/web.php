<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('central.find-workspace');
});

// Avoid 405 error if user visits /find-workspace via GET
Route::get('/find-workspace', function () {
    return redirect('/');
});

Route::post('/find-workspace', function (Illuminate\Http\Request $request) {
    $workspace = strtolower($request->workspace);

    return redirect("http://{$workspace}.localhost:8000/login");
})->name('central.find-workspace.post');

// Central Auth & Dashboard
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    // Identity Management (Central)
    Route::post('users/bulk', [\App\Http\Controllers\Platform\UserController::class, 'bulkAction'])->name('central.users.bulk');
    Route::post('users/{id}/restore', [\App\Http\Controllers\Platform\UserController::class, 'restore'])->name('central.users.restore');
    Route::resource('users', \App\Http\Controllers\Platform\UserController::class)->names('central.users');
    Route::resource('roles', \App\Http\Controllers\Platform\RoleController::class)->names('central.roles');
    Route::resource('permissions', \App\Http\Controllers\Platform\PermissionController::class)->names('central.permissions');

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\Platform\ActivityLogController::class, 'index'])->name('central.activity-logs.index');

    Route::patch('/tenants/{tenant}/toggle-status', [\App\Http\Controllers\Central\TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');
    Route::resource('tenants', \App\Http\Controllers\Central\TenantController::class);

    // Customer Management (Central/Global)
    Route::post('customers/bulk', [\App\Http\Controllers\Tenant\CustomerController::class, 'bulk'])->name('central.customers.bulk');
    Route::post('customers/{id}/restore', [\App\Http\Controllers\Tenant\CustomerController::class, 'restore'])->name('central.customers.restore');
    Route::resource('customers', \App\Http\Controllers\Tenant\CustomerController::class)->names('central.customers');

    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');
});
