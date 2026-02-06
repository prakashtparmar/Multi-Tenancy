<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $orderQuery = Order::where('status', '!=', 'cancelled');
        $customerQuery = Customer::query();
        $tenantQuery = Tenant::query();
        $period = $request->input('period', 'today'); // Default shift to 'today'

        // 1. Role-based isolation
        if (!$isSuperAdmin) {
            $orderQuery->where('created_by', $user->id);
            $customerQuery->where('created_by', $user->id);
            $tenantQuery->where('id', 0);
        }

        // 2. Time-based filtering
        $startDate = null;
        $endDate = null;
        $compareStartDate = null;
        $compareEndDate = null;

        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                $compareStartDate = now()->subDay()->startOfDay();
                $compareEndDate = now()->subDay()->endOfDay();
                break;
            case 'yesterday':
                $startDate = now()->subDay()->startOfDay();
                $endDate = now()->subDay()->endOfDay();
                $compareStartDate = now()->subDays(2)->startOfDay();
                $compareEndDate = now()->subDays(2)->endOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $compareStartDate = now()->subWeek()->startOfWeek();
                $compareEndDate = now()->subWeek()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $compareStartDate = now()->subMonth()->startOfMonth();
                $compareEndDate = now()->subMonth()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $compareStartDate = now()->subYear()->startOfYear();
                $compareEndDate = now()->subYear()->endOfYear();
                break;
            case '30days':
            default:
                $startDate = now()->subDays(30);
                $compareStartDate = now()->subDays(60);
                $compareEndDate = now()->subDays(30);
                $period = '30days';
                break;
        }

        $filteredOrderQuery = (clone $orderQuery);
        $filteredCustomerQuery = (clone $customerQuery);

        if ($startDate) {
            $filteredOrderQuery->where('created_at', '>=', $startDate);
            $filteredCustomerQuery->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $filteredOrderQuery->where('created_at', '<=', $endDate);
            $filteredCustomerQuery->where('created_at', '<=', $endDate);
        }

        $totalSales = (float) $filteredOrderQuery->sum('grand_total');
        $ordersCount = $filteredOrderQuery->count();
        $customersCount = $filteredCustomerQuery->count();
        $tenantsCount = $tenantQuery->count();

        // Calculate comparison for change percentage (Dynamic based on period)
        $duration = $startDate->diffInDays($endDate ?? now()) + 1;
        $compareStartDate = (clone $startDate)->subDays($duration);
        $compareEndDate = $endDate ? (clone $endDate)->subDays($duration) : (clone $startDate)->subSecond();

        $prevOrderQuery = (clone $orderQuery)->whereBetween('created_at', [$compareStartDate, $compareEndDate]);
        $prevSales = (float) $prevOrderQuery->sum('grand_total');

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

        $stats = [
            [
                'title' => 'Total Sales',
                'value' => 'Rs ' . number_format($totalSales, 2),
                'change' => ($salesChange >= 0 ? '+' : '') . number_format($salesChange, 1) . '%',
                'trend' => $salesChange >= 0 ? 'up' : 'down',
                'desc' => 'vs. ' . $periodLabel,
                'icon' => 'dollar-sign'
            ],
            [
                'title' => $isSuperAdmin ? 'Active Tenants' : 'My Records',
                'value' => $isSuperAdmin ? number_format($tenantsCount) : number_format($ordersCount + $customersCount),
                'change' => '',
                'trend' => 'up',
                'desc' => $isSuperAdmin ? 'Platform total' : 'Items in period',
                'icon' => $isSuperAdmin ? 'users' : 'refresh-cw'
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
                'title' => 'New Customers',
                'value' => number_format($customersCount),
                'change' => '',
                'trend' => 'up',
                'desc' => 'In selected period',
                'icon' => 'users'
            ],
        ];

        $recentOrders = (clone $filteredOrderQuery)->with(['customer', 'creator'])->latest()->take(5)->get();

        // Prepare chart data (based on duration)
        $chartDataDuration = $startDate->diffInDays($endDate ?? now()) + 1;
        $chartDataRaw = (clone $orderQuery)
            ->whereBetween('created_at', [$startDate, $endDate ?? now()])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(grand_total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $chartData = [];
        for ($i = $chartDataDuration - 1; $i >= 0; $i--) {
            $date = ($endDate ?? now())->subDays($i)->format('Y-m-d');
            $chartData[] = (float) ($chartDataRaw[$date] ?? 0);
        }

        $orderHistory = (clone $filteredOrderQuery)->with(['customer', 'creator'])->latest()->take(20)->get();

        // Admin/User Activity Tracking
        $onlineUsersQuery = \App\Models\User::query()
            ->withCount(['orders', 'customers'])
            ->withSum('orders as total_revenue', 'grand_total');

        // RESTRICTION: Non-Super Admins can ONLY see themselves in "Team Activity"
        if (!$isSuperAdmin) {
            $onlineUsersQuery->where('id', $user->id);
        }

        $onlineUsers = $onlineUsersQuery
            ->orderByRaw('CASE WHEN last_seen_at > ? THEN 1 ELSE 0 END DESC', [now()->subMinutes(5)])
            ->orderBy('last_seen_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard', compact('stats', 'recentOrders', 'chartData', 'orderHistory', 'period', 'onlineUsers'));
    }

    public function exportTeamActivity()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin');

        $query = \App\Models\User::query()
            ->withCount(['orders', 'customers'])
            ->withSum('orders as total_revenue', 'grand_total');

        if (!$isSuperAdmin) {
            $query->where('id', $user->id);
        }

        $users = $query->get();

        $csvFileName = 'team_activity_' . date('Y-m-d_H-i') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['User Name', 'Location', 'Status', 'Last Seen', 'Session Duration', 'Total Orders', 'Total Customers', 'Total Revenue']);

            foreach ($users as $u) {
                // Calculate approximate session duration
                $duration = 'N/A';
                if ($u->last_login_at && $u->last_seen_at) {
                    $duration = $u->last_login_at->diff($u->last_seen_at)->format('%Hh %Im');
                }

                fputcsv($file, [
                    $u->name,
                    $u->location ?? 'Unknown',
                    $u->isOnline() ? 'Online' : 'Offline',
                    $u->last_seen_at ? $u->last_seen_at->format('Y-m-d H:i:s') : 'Never',
                    $duration,
                    $u->orders_count,
                    $u->customers_count,
                    number_format($u->total_revenue ?? 0, 2)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
