<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use Illuminate\Http\Request;

class OrderReturnController extends Controller
{
    public function index()
    {
        return response()->json(OrderReturn::with('order')->latest()->paginate(20));
    }

    public function show(OrderReturn $orderReturn)
    {
        return response()->json($orderReturn->load(['items', 'order']));
    }
}
