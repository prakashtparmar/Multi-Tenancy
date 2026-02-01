<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the invoices.
     */
    public function index(): JsonResponse
    {
        $this->authorize('orders view');

        $invoices = Invoice::with(['order.customer'])->latest()->paginate(20);
        
        return response()->json($invoices);
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('orders view');

        return response()->json($invoice->load(['order', 'payments']));
    }
}
