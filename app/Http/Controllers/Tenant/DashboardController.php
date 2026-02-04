<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Period (DEFAULT = TODAY)
        $period = $request->query('period', 'today');

        // Date range resolver
        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;

            case 'yesterday':
                $startDate = now()->subDay()->startOfDay();
                $endDate = now()->subDay()->endOfDay();
                break;

            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;

            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;

            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;

            case '30days':
            default:
                $startDate = now()->subDays(29)->startOfDay();
                $endDate = now()->endOfDay();
                break;
        }

        // KPI logic (date-aware)
        $totalSales = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('grand_total');

        $ordersCount = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $customersCount = Customer::whereBetween('created_at', [$startDate, $endDate])->count();
        $productsCount = Product::whereBetween('created_at', [$startDate, $endDate])->count();

        // Sales comparison (Dynamic based on period)
        $duration = $startDate->diffInDays($endDate) + 1;
        $compareStartDate = (clone $startDate)->subDays($duration);
        $compareEndDate = (clone $endDate)->subDays($duration);

        $prevSales = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$compareStartDate, $compareEndDate])
            ->sum('grand_total');

        $salesChange = $prevSales > 0
            ? (($totalSales - $prevSales) / $prevSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        $periodLabel = match ($period) {
            'today' => 'yesterday',
            'yesterday' => 'day before',
            'week' => 'last week',
            'month' => 'last month',
            'year' => 'last year',
            default => 'previous ' . $duration . ' days',
        };

        // Stats array
        $stats = [
            [
                'title' => 'Gross Sales',
                'value' => 'Rs ' . number_format($totalSales, 2),
                'change' => ($salesChange >= 0 ? '+' : '') . number_format($salesChange, 1) . '%',
                'trend' => $salesChange >= 0 ? 'up' : 'down',
                'desc' => 'vs. ' . $periodLabel,
                'icon' => 'dollar-sign'
            ],
            [
                'title' => 'Orders',
                'value' => number_format($ordersCount),
                'change' => '',
                'trend' => 'up',
                'desc' => 'In selected period',
                'icon' => 'shopping-cart'
            ],
            [
                'title' => 'Customers',
                'value' => number_format($customersCount),
                'change' => '',
                'trend' => 'up',
                'desc' => 'New registrations',
                'icon' => 'users'
            ],
            [
                'title' => 'Products',
                'value' => number_format($productsCount),
                'change' => '',
                'trend' => 'up',
                'desc' => 'Active inventory',
                'icon' => 'refresh-cw'
            ],
        ];

        // Recent Orders (date-aware)
        $recentOrders = Order::with('customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->take(5)
            ->get();

        // Chart Data (date-aware)
        $chartDataRaw = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(grand_total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $chartData = [];
        $days = match ($period) {
            'today' => 1,
            'week' => 7,
            'month' => now()->daysInMonth,
            'year' => 12,
            default => 30,
        };

        for ($i = $days - 1; $i >= 0; $i--) {
            // For year, we might want to show months, but let's stick to days for now to keep charts consistent
            // or switch to month-based if period is 'year'
            $date = (clone $endDate)->subDays($i)->format('Y-m-d');
            $chartData[] = (float) ($chartDataRaw[$date] ?? 0);
        }

        // ✅ REQUIRED ADDITION (ORDER HISTORY) - NOW FILTERED
        $orderHistory = Order::whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        return view('dashboard', compact(
            'stats',
            'recentOrders',
            'chartData',
            'orderHistory',
            'period'
        ));
    }
}
