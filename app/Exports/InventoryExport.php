<?php

namespace App\Exports;

use App\Models\InventoryStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return InventoryStock::with(['product', 'warehouse'])->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Warehouse',
            'Quantity',
            'Reserved Quantity',
            'Available Quantity'
        ];
    }

    public function map($stock): array
    {
        return [
            $stock->product?->name ?? 'N/A',
            $stock->product?->sku ?? 'N/A',
            $stock->warehouse?->name ?? 'N/A',
            $stock->quantity,
            $stock->reserve_quantity,
            $stock->quantity - $stock->reserve_quantity
        ];
    }
}
