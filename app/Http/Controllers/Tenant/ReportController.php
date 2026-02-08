<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        // Date Logic
        $range = $request->input('range', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($range === 'this_month') {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        } elseif ($range === 'last_month') {
            $start = Carbon::now()->subMonth()->startOfMonth();
            $end = Carbon::now()->subMonth()->endOfMonth();
        } elseif ($range === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            // Default to this month
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        }

        // 1. Revenue (Completed/Paid Orders)
        // We only count revenue from valid orders (not cancelled).
        // For strict P&L, usually based on Invoice Issue Date (Accrual) or Payment Date (Cash).
        // Simplifying to Order Placed Date for now, assuming 'delivered' or 'completed' means revenue realized.
        // Or strictly 'paid' payment status. Let's use 'paid' status for conservative revenue.
        $orders = Order::with('items')
            ->whereBetween('placed_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->get();

        $revenue = $orders->sum('grand_total');

        // 2. COGS (Cost of Goods Sold)
        $cogs = 0;
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                // cost_price stored in item * quantity
                $cogs += ($item->cost_price * $item->quantity);
            }
        }

        // 3. Gross Profit
        $grossProfit = $revenue - $cogs;

        // 4. Expenses
        $expenses = Expense::whereBetween('date', [$start, $end])->get();
        $totalExpenses = $expenses->sum('amount');
        $expenseBreakdown = $expenses->groupBy('category')->map->sum('amount');

        // 5. Net Profit
        $netProfit = $grossProfit - $totalExpenses;

        return view('tenant.reports.profit-loss', compact(
            'revenue',
            'cogs',
            'grossProfit',
            'totalExpenses',
            'netProfit',
            'start',
            'end',
            'range',
            'expenseBreakdown'
        ));
    }
}
