<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Order::with('customer')->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Date',
            'Customer',
            'Status',
            'Total',
            'Payment Status'
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('Y-m-d'),
            $order->customer?->name ?? 'N/A',
            ucfirst($order->status),
            $order->grand_total,
            ucfirst($order->payment_status)
        ];
    }
}
