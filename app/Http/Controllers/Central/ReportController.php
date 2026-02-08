<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function profitLoss(Request $request)
    {
        $this->authorize('finance view');

        $range = $request->input('range', 'this_month');
        $startDate = null;
        $endDate = null;

        switch ($range) {
            case 'this_month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = now()->subMonth()->startOfMonth();
                $endDate = now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                $startDate = $request->input('start_date') ? \Carbon\Carbon::parse($request->input('start_date')) : now()->startOfMonth();
                $endDate = $request->input('end_date') ? \Carbon\Carbon::parse($request->input('end_date')) : now()->endOfMonth();
                break;
            default:
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
        }

        // 1. Revenue (Paid Orders)
        $revenue = Order::whereIn('status', ['confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('grand_total');

        // 2. COGS (Cost of Goods Sold)
        // We need to join orders to filter by date/status
        $cogs = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereIn('status', ['confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate]);
        })->sum(DB::raw('quantity * COALESCE(cost_price, 0)'));

        // 3. Expenses
        $expenses = Expense::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->get();
        $totalExpenses = $expenses->sum('amount');

        // 4. Calculations
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $totalExpenses;

        // Breakdown
        $expenseBreakdown = $expenses->groupBy('category')->map(function ($row) {
            return $row->sum('amount');
        });

        return view('central.reports.profit-loss', compact(
            'revenue',
            'cogs',
            'grossProfit',
            'totalExpenses',
            'netProfit',
            'expenseBreakdown',
            'range'
        ));
    }
}
