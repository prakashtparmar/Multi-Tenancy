<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\ChatController;

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
    Route::get('/dashboard', [\App\Http\Controllers\Tenant\DashboardController::class, 'index'])->name('tenant.dashboard');

    Route::get('/settings', function () {
        return view('settings');
    })->name('tenant.settings');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('tenant.notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('tenant.notifications.read-all');

    // Identity Management
    Route::post('users/bulk', [\App\Http\Controllers\Platform\UserController::class, 'bulkAction'])->name('tenant.users.bulk');
    Route::post('users/{id}/restore', [\App\Http\Controllers\Platform\UserController::class, 'restore'])->name('tenant.users.restore');
    Route::resource('users', \App\Http\Controllers\Platform\UserController::class)->names('tenant.users');
    Route::resource('roles', \App\Http\Controllers\Platform\RoleController::class)->names('tenant.roles');
    Route::resource('permissions', \App\Http\Controllers\Platform\PermissionController::class)->names('tenant.permissions');

    // CRM
    Route::post('customers/bulk', [\App\Http\Controllers\Tenant\CustomerController::class, 'bulk'])->name('tenant.customers.bulk');
    Route::post('customers/{id}/restore', [\App\Http\Controllers\Tenant\CustomerController::class, 'restore'])->name('tenant.customers.restore');
    Route::post('customers/{customer}/interaction', [\App\Http\Controllers\Tenant\CustomerController::class, 'storeInteraction'])->name('tenant.customers.interaction');
    Route::resource('customers', \App\Http\Controllers\Tenant\CustomerController::class)->names('tenant.customers');

    // Enterprise Modules
    Route::patch('shipments/{shipment}/status', [\App\Http\Controllers\Tenant\ShipmentController::class, 'updateStatus'])->name('tenant.shipments.update-status');
    Route::resource('shipments', \App\Http\Controllers\Tenant\ShipmentController::class)->names('tenant.shipments');

    Route::patch('returns/{orderReturn}/status', [\App\Http\Controllers\Tenant\OrderReturnController::class, 'updateStatus'])->name('tenant.returns.update-status');
    Route::resource('returns', \App\Http\Controllers\Tenant\OrderReturnController::class)->names('tenant.returns');

    Route::post('invoices/{invoice}/payment', [\App\Http\Controllers\Tenant\InvoiceController::class, 'addPayment'])->name('tenant.invoices.add-payment');
    Route::resource('invoices', \App\Http\Controllers\Tenant\InvoiceController::class)->only(['index', 'store', 'show'])->names('tenant.invoices');

    Route::post('purchase-orders/{purchaseOrder}/receive', [\App\Http\Controllers\Tenant\PurchaseOrderController::class, 'receive'])->name('tenant.purchase-orders.receive');
    Route::resource('purchase-orders', \App\Http\Controllers\Tenant\PurchaseOrderController::class)->names('tenant.purchase-orders');


    // Purchase Orders
    Route::post('purchase-orders/{purchaseOrder}/receive', [\App\Http\Controllers\Tenant\PurchaseOrderController::class, 'receive'])->name('tenant.purchase-orders.receive');
    Route::resource('purchase-orders', \App\Http\Controllers\Tenant\PurchaseOrderController::class)->names('tenant.purchase-orders');

    // Chat System (Tenant)
    Route::prefix('chat')->group(function () {
        Route::get('group/view_members', [ChatController::class, 'viewGroupMembers'])->name('tenant.chatgroup.view_members');
        Route::post('group/update/{id}', [ChatController::class, 'updateGroup'])->name('tenant.chatgroup.update_group');
        Route::get('group/get_group', [ChatController::class, 'getGroup'])->name('tenant.chatgroup.get_group');
        Route::get('get_chat', [ChatController::class, 'getChat'])->name('tenant.userchat.get_chat');
        Route::get('get_users', [ChatController::class, 'getUsers'])->name('tenant.userchat.get_users');
        Route::post('mark_as_read', [ChatController::class, 'markAsRead'])->name('tenant.chatgroup.mark_as_read');
        Route::post('mark_as_starred', [ChatController::class, 'markAsStarred'])->name('tenant.userchat.mark_as_starred');
        Route::post('forward_msg', [ChatController::class, 'forwardMsg'])->name('tenant.userchat.forward_msg');

        // Chat Group Resources
        Route::get('group', [ChatController::class, 'indexGroup'])->name('tenant.chatgroup.index');
        Route::post('group/add_members/{id}', [ChatController::class, 'addMembers'])->name('tenant.chatgroup.add_members');
        Route::post('group/remove_members/{id}', [ChatController::class, 'removeMembers'])->name('tenant.chatgroup.remove_members');
        Route::post('group', [ChatController::class, 'storeGroup'])->name('tenant.chatgroup.store');
        Route::delete('group/{id}', [ChatController::class, 'destroyGroup'])->name('tenant.chatgroup.destroy');

        // User Chat Resources
        Route::get('/', [ChatController::class, 'indexChat'])->name('tenant.userchat.index');
        Route::post('/', [ChatController::class, 'storeChat'])->name('tenant.userchat.store');
    });

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\Platform\ActivityLogController::class, 'index'])->name('tenant.activity-logs.index');

    // Enterprise Modules
    Route::get('products/search', [\App\Http\Controllers\Tenant\ProductController::class, 'search'])->name('tenant.products.search');
    Route::resource('products', \App\Http\Controllers\Tenant\ProductController::class)->names('tenant.products');
    Route::resource('categories', \App\Http\Controllers\Tenant\CategoryController::class)->names('tenant.categories');
    Route::resource('brands', \App\Http\Controllers\Tenant\BrandController::class)->names('tenant.brands');

    Route::resource('expenses', \App\Http\Controllers\Tenant\ExpenseController::class)->names('tenant.expenses');
    Route::get('reports/profit-loss', [\App\Http\Controllers\Tenant\ReportController::class, 'profitLoss'])->name('tenant.reports.profit-loss');

    Route::resource('warehouses', \App\Http\Controllers\Tenant\WarehouseController::class)->names('tenant.warehouses');
    Route::resource('suppliers', \App\Http\Controllers\Tenant\SupplierController::class)->names('tenant.suppliers');
    Route::post('orders/{order}/status', [\App\Http\Controllers\Tenant\OrderController::class, 'updateStatus'])->name('tenant.orders.status');
    Route::get('orders/{order}/invoice', [\App\Http\Controllers\Tenant\OrderController::class, 'downloadInvoice'])->name('tenant.orders.invoice');
    Route::get('orders/{order}/receipt', [\App\Http\Controllers\Tenant\OrderController::class, 'downloadReceipt'])->name('tenant.orders.receipt');
    Route::post('orders/export', [\App\Http\Controllers\Tenant\OrderController::class, 'export'])->name('tenant.orders.export');

    // Order Processing (Warehouse)
    Route::get('processing/orders', [\App\Http\Controllers\Tenant\OrderProcessingController::class, 'index'])->name('tenant.processing.orders.index');
    Route::post('processing/orders/{order}/process', [\App\Http\Controllers\Tenant\OrderProcessingController::class, 'process'])->name('tenant.processing.orders.process');
    Route::post('processing/orders/{order}/ready', [\App\Http\Controllers\Tenant\OrderProcessingController::class, 'readyToShip'])->name('tenant.processing.orders.ready');
    Route::post('processing/orders/{order}/dispatch', [\App\Http\Controllers\Tenant\OrderProcessingController::class, 'dispatch'])->name('tenant.processing.orders.dispatch');
    Route::post('processing/orders/bulk-print', [\App\Http\Controllers\Tenant\OrderProcessingController::class, 'bulkPrint'])->name('tenant.processing.orders.bulk-print');
    Route::post('processing/orders/bulk-status', [\App\Http\Controllers\Tenant\OrderProcessingController::class, 'bulkStatusUpdate'])->name('tenant.processing.orders.bulk-status');
    Route::post('processing/orders/bulk-dispatch', [\App\Http\Controllers\Tenant\OrderProcessingController::class, 'bulkDispatch'])->name('tenant.processing.orders.bulk-dispatch');

    Route::get('processing/returns', [\App\Http\Controllers\Tenant\OrderProcessingController::class, 'indexReturns'])->name('tenant.processing.returns.index');
    Route::post('processing/returns/{orderReturn}/receive', [\App\Http\Controllers\Tenant\OrderProcessingController::class, 'receiveReturn'])->name('tenant.processing.returns.receive');

    // Order Verification
    Route::get('orders/verification', [\App\Http\Controllers\Tenant\OrderVerificationController::class, 'index'])
        ->name('tenant.orders.verification.index');
    Route::post('orders/{order}/verification', [\App\Http\Controllers\Tenant\OrderVerificationController::class, 'store'])
        ->name('tenant.orders.verification.store');

    Route::resource('orders', \App\Http\Controllers\Tenant\OrderController::class)->names('tenant.orders');

    // Inventory Management
    Route::get('inventory', [\App\Http\Controllers\Tenant\InventoryController::class, 'index'])->name('tenant.inventory.index');
    Route::post('inventory/adjust', [\App\Http\Controllers\Tenant\InventoryController::class, 'adjust'])->name('tenant.inventory.adjust');

    // Stock Transfers
    Route::get('stock-transfers', [\App\Http\Controllers\Tenant\StockTransferController::class, 'index'])->name('tenant.stock-transfers.index');
    Route::post('stock-transfers', [\App\Http\Controllers\Tenant\StockTransferController::class, 'store'])->name('tenant.stock-transfers.store');

    // Search Endpoints (AJAX) - "Command Center" Functionality
    Route::prefix('api')->group(function () {
        Route::get('search/customers', [\App\Http\Controllers\Tenant\SearchController::class, 'customers'])
            ->name('tenant.api.search.customers');
        Route::post('customers/quick', [\App\Http\Controllers\Tenant\SearchController::class, 'storeCustomer'])
            ->name('tenant.api.customers.store-quick');
        Route::post('addresses/store', [\App\Http\Controllers\Tenant\SearchController::class, 'storeAddress'])
            ->name('tenant.api.addresses.store');
        Route::get('search/products', [\App\Http\Controllers\Tenant\SearchController::class, 'products'])
            ->name('tenant.api.search.products');
        Route::get('village-lookup', [\App\Http\Controllers\VillageController::class, 'lookup'])
            ->name('tenant.api.village-lookup');
    });
});



// Root redirect
Route::get('/', function () {
    // Explicitly use the current host for redirects to avoid any central/tenant confusion
    $protocol = request()->secure() ? 'https://' : 'http://';
    $host = request()->getHost();
    $port = request()->getPort() ? ':' . request()->getPort() : '';
    $baseUrl = $protocol . $host . $port;

    if (!auth()->check()) {
        return redirect($baseUrl . '/login');
    }

    return redirect($baseUrl . '/dashboard');
});
