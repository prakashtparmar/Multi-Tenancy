<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Order::with(['customer', 'items', 'billingAddress', 'shippingAddress', 'creator', 'updater', 'shipments'])->latest();

        // Apply Status Filter
        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }
        // Default Logic for Tenant/Central Index if status is missing/null (Active Orders)
        // However, export might be expected to export ALL if nothing is selected? 
        // The user request says "insted of only active tabs order details fix this issue" -> "while export from this index it will export all... instead of only active... fix this"
        // So user WANTS it to filter.
        // If status is not set, the Index controller defaults to ['confirmed', 'processing', 'ready_to_ship'].
        // We should replicate that default if no status is passed, OR rely on the controller to pass 'all' or specific statuses.
        // Let's assume the controller will pass the exact filter state. 
        elseif (!isset($this->filters['status'])) {
            // Replicating OrderProcessingController default behavior
            $query->whereIn('status', ['confirmed', 'processing', 'ready_to_ship']);
        }


        // Apply Search Filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply Date Filter
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        // Apply IDs Filter (Selected Records)
        if (!empty($this->filters['ids']) && is_array($this->filters['ids'])) {
            $query->whereIn('id', $this->filters['ids']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'order_number',
            'courier',
            'tracking_number',
            // Context Columns
            'Status',
            'Placed At',
            'Customer Name',
            'Customer Email',
            'Customer Mobile',
            'Payment Status',
            'Total Items',
            'Grand Total',
            // Billing Address
            'Billing Line 1',
            'Billing Line 2',
            'Billing City',
            'Billing State',
            'Billing Pincode',
            // Shipping Address
            'Shipping Line 1',
            'Shipping Line 2',
            'Shipping City',
            'Shipping State',
            'Shipping Pincode',
        ];
    }

    public function map($order): array
    {
        // Handle multiple shipments if present
        $couriers = $order->shipments->pluck('carrier')->filter()->unique()->implode(', ');
        $trackingNumbers = $order->shipments->pluck('tracking_number')->filter()->unique()->implode(', ');

        return [
            $order->order_number,
            $couriers,
            $trackingNumbers,
            ucfirst($order->status),
            $order->created_at->format('Y-m-d H:i:s'),
            $order->customer?->name ?? 'N/A',
            $order->customer?->email ?? 'N/A',
            $order->customer?->mobile ?? 'N/A',
            ucfirst($order->payment_status),
            $order->items->count(),
            $order->grand_total,
            // Billing
            $order->billingAddress?->address_line1 ?? '',
            $order->billingAddress?->address_line2 ?? '',
            $order->billingAddress?->city ?? $order->billingAddress?->district ?? '',
            $order->billingAddress?->state ?? '',
            $order->billingAddress?->pincode ?? '',
            // Shipping
            $order->shippingAddress?->address_line1 ?? '',
            $order->shippingAddress?->address_line2 ?? '',
            $order->shippingAddress?->city ?? $order->shippingAddress?->district ?? '',
            $order->shippingAddress?->state ?? '',
            $order->shippingAddress?->pincode ?? '',
        ];
    }
}
