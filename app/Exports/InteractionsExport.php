<?php

namespace App\Exports;

use App\Models\CustomerInteraction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InteractionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = CustomerInteraction::with(['customer', 'user'])->latest();

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Customer Name',
            'Customer Email',
            'Customer Mobile',
            'Interaction Type',
            'Outcome',
            'Conducted By',
            'Notes',
            'Metadata'
        ];
    }

    public function map($interaction): array
    {
        return [
            $interaction->created_at->format('Y-m-d H:i:s'),
            $interaction->customer?->name ?? 'N/A',
            $interaction->customer?->email ?? 'N/A',
            $interaction->customer?->mobile ?? 'N/A',
            ucfirst(str_replace('_', ' ', $interaction->type)),
            ucfirst(str_replace('_', ' ', $interaction->outcome ?? 'N/A')),
            $interaction->user?->name ?? 'System',
            $interaction->notes ?? '',
            $interaction->metadata ? json_encode($interaction->metadata) : ''
        ];
    }
}
