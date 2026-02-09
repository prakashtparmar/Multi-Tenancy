<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Workspace Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('central.find-workspace');
});

// Avoid 405 error if user visits /find-workspace via GET
Route::get('/find-workspace', function () {
    return redirect('/');
});

Route::post('/find-workspace', function (Illuminate\Http\Request $request) {
    $request->validate([
        'workspace' => 'required|alpha_dash|max:64',
    ]);

    $workspace = strtolower($request->workspace);

    $scheme = $request->getScheme();
    $host = $request->getHost();
    $port = $request->getPort() ? ':' . $request->getPort() : '';

    // Handle local dev vs production dynamic domains
    $baseDomain = ($host === '127.0.0.1') ? 'localhost' : $host;

    return redirect("{$scheme}://{$workspace}.{$baseDomain}{$port}/login");
})->name('central.find-workspace.post');

/*
|--------------------------------------------------------------------------
| Central Auth
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login'])
    ->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/


Route::get(
    'api/village-lookup',
    [\App\Http\Controllers\VillageController::class, 'lookup']
)->name('central.api.village-lookup');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Central\DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/dashboard/export-team-activity', [\App\Http\Controllers\Central\DashboardController::class, 'exportTeamActivity'])
        ->name('central.team.export');

    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('central.notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('central.notifications.read-all');


    /*
    |--------------------------------------------------------------------------
    | Identity Management (Central)
    |--------------------------------------------------------------------------
    */

    Route::post('users/bulk', [\App\Http\Controllers\Platform\UserController::class, 'bulkAction'])
        ->name('central.users.bulk');
    Route::post('users/{id}/restore', [\App\Http\Controllers\Platform\UserController::class, 'restore'])
        ->name('central.users.restore');
    Route::resource('users', \App\Http\Controllers\Platform\UserController::class)
        ->names('central.users');

    Route::resource('roles', \App\Http\Controllers\Platform\RoleController::class)
        ->names('central.roles');
    Route::resource('permissions', \App\Http\Controllers\Platform\PermissionController::class)
        ->names('central.permissions');

    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */

    Route::get('/activity-logs', [\App\Http\Controllers\Platform\ActivityLogController::class, 'index'])
        ->name('central.activity-logs.index');

    /*
    |--------------------------------------------------------------------------
    | Tenants
    |--------------------------------------------------------------------------
    */

    Route::patch('/tenants/{tenant}/toggle-status', [\App\Http\Controllers\Central\TenantController::class, 'toggleStatus'])
        ->name('tenants.toggle-status');
    Route::resource('tenants', \App\Http\Controllers\Central\TenantController::class);

    /*
    |--------------------------------------------------------------------------
    | Customer Management (Central)
    |--------------------------------------------------------------------------
    */

    Route::post('customers/bulk', [\App\Http\Controllers\Central\CustomerController::class, 'bulk'])
        ->name('central.customers.bulk');
    Route::post('customers/{id}/restore', [\App\Http\Controllers\Central\CustomerController::class, 'restore'])
        ->name('central.customers.restore');
    Route::post('customers/{customer}/interaction', [\App\Http\Controllers\Central\CustomerController::class, 'storeInteraction'])
        ->name('central.customers.interaction');
    Route::resource('customers', \App\Http\Controllers\Central\CustomerController::class)
        ->names('central.customers');

    /*
    |--------------------------------------------------------------------------
    | 🔹 ADD THIS ROUTE (Village Auto-Fetch – SAFE)
    |--------------------------------------------------------------------------
    | Used by customer create/edit form
    | Does NOT affect any existing logic
    */





    /*
    |--------------------------------------------------------------------------
    | Reporting Module
    |--------------------------------------------------------------------------
    */
    Route::get('/reports', [\App\Http\Controllers\Central\ReportController::class, 'index'])->name('central.reports.index');
    Route::post('/reports/export', [\App\Http\Controllers\Central\ReportController::class, 'export'])->name('central.reports.export');

    /*
    |--------------------------------------------------------------------------
    | Enterprise Modules (Central)
    |--------------------------------------------------------------------------
    */

    Route::resource('categories', \App\Http\Controllers\Central\CategoryController::class)
        ->names('central.categories');
    Route::resource('brands', \App\Http\Controllers\Central\BrandController::class)
        ->names('central.brands');

    Route::resource('products', \App\Http\Controllers\Central\ProductController::class)
        ->names('central.products');
    Route::resource('warehouses', \App\Http\Controllers\Central\WarehouseController::class)
        ->names('central.warehouses');
    Route::resource('suppliers', \App\Http\Controllers\Central\SupplierController::class)
        ->names('central.suppliers');

    Route::patch('shipments/{shipment}/status', [\App\Http\Controllers\Central\ShipmentController::class, 'updateStatus'])
        ->name('central.shipments.update-status');
    Route::resource('shipments', \App\Http\Controllers\Central\ShipmentController::class)
        ->names('central.shipments');

    Route::patch('returns/{orderReturn}/status', [\App\Http\Controllers\Central\OrderReturnController::class, 'updateStatus'])
        ->name('central.returns.update-status');

    // Inspection Routes
    Route::get('returns/{return}/inspect', [\App\Http\Controllers\Central\OrderReturnController::class, 'inspect'])
        ->name('central.returns.inspect');
    Route::post('returns/{return}/inspect', [\App\Http\Controllers\Central\OrderReturnController::class, 'storeInspection'])
        ->name('central.returns.inspect.store');

    // Refund Routes
    Route::get('returns/{return}/refund', [\App\Http\Controllers\Central\OrderReturnController::class, 'refund'])
        ->name('central.returns.refund');
    Route::post('returns/{return}/refund', [\App\Http\Controllers\Central\OrderReturnController::class, 'storeRefund'])
        ->name('central.returns.refund.store');

    Route::resource('returns', \App\Http\Controllers\Central\OrderReturnController::class)
        ->names('central.returns');

    Route::resource('expenses', \App\Http\Controllers\Central\ExpenseController::class)->names('central.expenses');
    Route::get('reports/profit-loss', [\App\Http\Controllers\Central\ReportController::class, 'profitLoss'])->name('central.reports.profit-loss');

    Route::post('invoices/{invoice}/payment', [\App\Http\Controllers\Central\InvoiceController::class, 'addPayment'])
        ->name('central.invoices.add-payment');


    Route::get(
        'invoices/{invoice}/pdf',
        [\App\Http\Controllers\Central\InvoiceController::class, 'pdf']
    )->name('central.invoices.pdf');




    Route::resource('invoices', \App\Http\Controllers\Central\InvoiceController::class)
        ->only(['index', 'store', 'show'])
        ->names('central.invoices');

    Route::post('purchase-orders/{purchaseOrder}/receive', [\App\Http\Controllers\Central\PurchaseOrderController::class, 'receive'])
        ->name('central.purchase-orders.receive');
    Route::resource('purchase-orders', \App\Http\Controllers\Central\PurchaseOrderController::class)
        ->names('central.purchase-orders');

    /*
    |--------------------------------------------------------------------------
    | Search Endpoints (AJAX)
    |--------------------------------------------------------------------------
    */

    Route::get('api/search/customers', [\App\Http\Controllers\Central\SearchController::class, 'customers'])
        ->name('central.api.search.customers');
    Route::post('api/customers/quick', [\App\Http\Controllers\Central\SearchController::class, 'storeCustomer'])
        ->name('central.api.customers.store-quick');
    Route::post('api/addresses/store', [\App\Http\Controllers\Central\SearchController::class, 'storeAddress'])
        ->name('central.api.addresses.store');
    Route::get('api/search/products', [\App\Http\Controllers\Central\SearchController::class, 'products'])
        ->name('central.api.search.products');
    Route::get('api/search/customer-orders', [\App\Http\Controllers\Central\SearchController::class, 'customerOrders'])
        ->name('central.api.search.customer-orders');
    Route::get('api/search/all-orders', [\App\Http\Controllers\Central\SearchController::class, 'allOrders'])
        ->name('central.api.search.all-orders');
    Route::get('api/search/customer-activity', [\App\Http\Controllers\Central\SearchController::class, 'customerActivity'])
        ->name('central.api.search.customer-activity');

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */

    Route::post('orders/{order}/update-status', [\App\Http\Controllers\Central\OrderController::class, 'updateStatus'])
        ->name('central.orders.update-status');
    Route::get('orders/{order}/receipt', [\App\Http\Controllers\Central\OrderController::class, 'downloadReceipt'])
        ->name('central.orders.receipt');
    Route::post('orders/export', [\App\Http\Controllers\Central\OrderController::class, 'export'])
        ->name('central.orders.export');
    Route::post('orders/bulk-print', [\App\Http\Controllers\Central\OrderController::class, 'bulkPrint'])
        ->name('central.orders.bulk-print');
    Route::resource('orders', \App\Http\Controllers\Central\OrderController::class)
        ->names('central.orders');

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    Route::get('inventory', [\App\Http\Controllers\Central\InventoryController::class, 'index'])
        ->name('central.inventory.index');
    Route::post('inventory/adjust', [\App\Http\Controllers\Central\InventoryController::class, 'adjust'])
        ->name('central.inventory.adjust');

    // Stock Transfers
    Route::get('stock-transfers', [\App\Http\Controllers\Central\StockTransferController::class, 'index'])->name('central.stock-transfers.index');
    Route::post('stock-transfers', [\App\Http\Controllers\Central\StockTransferController::class, 'store'])->name('central.stock-transfers.store');

    /*
    |--------------------------------------------------------------------------
    | Interaction Outcomes
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'api/central', 'as' => 'central.api.'], function () {
        Route::get('outcomes', [\App\Http\Controllers\Central\InteractionOutcomeController::class, 'index'])->name('outcomes.index');
        Route::post('outcomes', [\App\Http\Controllers\Central\InteractionOutcomeController::class, 'store'])->name('outcomes.store');
        Route::put('outcomes/{outcome}', [\App\Http\Controllers\Central\InteractionOutcomeController::class, 'update'])->name('outcomes.update');
        Route::delete('outcomes/{outcome}', [\App\Http\Controllers\Central\InteractionOutcomeController::class, 'destroy'])->name('outcomes.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])
        ->name('logout');
});
