<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        return response()->json(Invoice::with('order.customer')->latest()->paginate(20));
    }

    public function show(Invoice $invoice)
    {
        return response()->json($invoice->load(['order', 'payments']));
    }
}
