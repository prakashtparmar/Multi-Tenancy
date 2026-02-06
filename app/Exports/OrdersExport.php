<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Order::with(['customer', 'items', 'billingAddress', 'shippingAddress', 'creator', 'updater'])->latest();

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Placed At',
            'Placed By',
            'Customer Name',
            'Customer Email',
            'Customer Mobile',
            'Status',
            'Payment Status',
            'Total Items',
            'Subtotal',
            'Tax',
            'Discount',
            'Shipping',
            'Grand Total',
            'Last Updated At',
            'Last Updated By',
            // Billing Address
            'Billing Line 1',
            'Billing Line 2',
            'Billing Village',
            'Billing Taluka',
            'Billing District',
            'Billing State',
            'Billing Pincode',
            'Billing Country',
            // Shipping Address
            'Shipping Line 1',
            'Shipping Line 2',
            'Shipping Village',
            'Shipping Taluka',
            'Shipping District',
            'Shipping State',
            'Shipping Pincode',
            'Shipping Country',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('Y-m-d H:i:s'),
            $order->creator?->name ?? 'System/Customer',
            $order->customer?->name ?? 'N/A',
            $order->customer?->email ?? 'N/A',
            $order->customer?->mobile ?? 'N/A',
            ucfirst($order->status),
            ucfirst($order->payment_status),
            $order->items->count(),
            $order->total_amount,
            $order->tax_amount,
            $order->discount_amount,
            $order->shipping_amount,
            $order->grand_total,
            $order->updated_at->format('Y-m-d H:i:s'),
            $order->updater?->name ?? 'System',
            // Billing
            $order->billingAddress?->address_line1 ?? '',
            $order->billingAddress?->address_line2 ?? '',
            $order->billingAddress?->village ?? '',
            $order->billingAddress?->taluka ?? '',
            $order->billingAddress?->district ?? '',
            $order->billingAddress?->state ?? '',
            $order->billingAddress?->pincode ?? '',
            $order->billingAddress?->country ?? '',
            // Shipping
            $order->shippingAddress?->address_line1 ?? '',
            $order->shippingAddress?->address_line2 ?? '',
            $order->shippingAddress?->village ?? '',
            $order->shippingAddress?->taluka ?? '',
            $order->shippingAddress?->district ?? '',
            $order->shippingAddress?->state ?? '',
            $order->shippingAddress?->pincode ?? '',
            $order->shippingAddress?->country ?? '',
        ];
    }
}
