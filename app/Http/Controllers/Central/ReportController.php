<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use App\Exports\InventoryExport;
use App\Exports\CustomersExport;
use App\Exports\InteractionsExport;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('finance view'); // Assuming same permission for now, or use a specific one if available
        return view('central.reports.index');
    }

    public function export(Request $request)
    {
        $this->authorize('finance view');

        $request->validate([
            'report_type' => 'required|string|in:orders,inventory,customers,interactions',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|string|in:csv,xlsx,pdf',
        ]);

        $reportType = $request->input('report_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $format = $request->input('format');

        $fileName = $reportType . '_report_' . now()->format('Y-m-d_H-i-s');
        $exportClass = null;

        switch ($reportType) {
            case 'orders':
                $exportClass = new OrdersExport($startDate, $endDate);
                break;
            case 'inventory':
                // InventoryExport doesn't support date filtering matching its constructor
                $exportClass = new InventoryExport();
                break;
            case 'customers':
                $exportClass = new CustomersExport($startDate, $endDate);
                break;
            case 'interactions':
                $exportClass = new InteractionsExport($startDate, $endDate);
                break;
        }

        if (!$exportClass) {
            return back()->with('error', 'Invalid report type selected.');
        }

        switch ($format) {
            case 'xlsx':
                return Excel::download($exportClass, $fileName . '.xlsx');
            case 'pdf':
                return Excel::download($exportClass, $fileName . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            case 'csv':
            default:
                return Excel::download($exportClass, $fileName . '.csv');
        }
    }

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
