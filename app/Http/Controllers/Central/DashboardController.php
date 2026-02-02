<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSales = Order::where('status', '!=', 'cancelled')->sum('grand_total');
        $ordersCount = Order::count();
        $customersCount = Customer::count();
        $tenantsCount = Tenant::count();

        // Calculate changes (mocking for now as we don't have historical snapshots easily, 
        // but let's try to get last 30 days vs 30-60 days)
        $sales30Days = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('grand_total');
        
        $salesPrev30Days = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))
            ->sum('grand_total');

        $salesChange = $salesPrev30Days > 0 
            ? (($sales30Days - $salesPrev30Days) / $salesPrev30Days) * 100 
            : 100;

        $stats = [
            [
                'title' => 'Total Sales',
                'value' => 'Rs ' . number_format($totalSales, 2),
                'change' => ($salesChange >= 0 ? '+' : '') . number_format($salesChange, 1) . '%',
                'trend' => $salesChange >= 0 ? 'up' : 'down',
                'desc' => 'vs. previous 30 days',
                'icon' => 'dollar-sign'
            ],
            [
                'title' => 'Active Tenants',
                'value' => number_format($tenantsCount),
                'change' => '+100%',
                'trend' => 'up',
                'desc' => 'Total registered',
                'icon' => 'users'
            ],
            [
                'title' => 'Total Orders',
                'value' => number_format($ordersCount),
                'change' => '+100%',
                'trend' => 'up',
                'desc' => 'Lifetime orders',
                'icon' => 'shopping-cart'
            ],
            [
                'title' => 'Total Customers',
                'value' => number_format($customersCount),
                'change' => '+100%',
                'trend' => 'up',
                'desc' => 'Registered customers',
                'icon' => 'users'
            ],
        ];

        $recentOrders = Order::with('customer')->latest()->take(5)->get();
        
        // Prepare chart data (last 30 days)
        $chartDataRaw = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(grand_total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData[] = $chartDataRaw[$date] ?? 0;
        }

        return view('dashboard', compact('stats', 'recentOrders', 'chartData'));
    }
}
