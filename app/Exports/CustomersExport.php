<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
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
        // Eager load addresses
        $query = Customer::with('addresses')->latest();

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Customer Code',
            'Name',
            'Email',
            'Mobile',
            'Outstanding Balance',
            'Credit Limit',
            'Joined Date',
            'Status',
            // Default Address
            'Address Line 1',
            'Address Line 2',
            'Village',
            'Taluka',
            'District',
            'State',
            'Pincode',
            'Country'
        ];
    }

    public function map($customer): array
    {
        // Try to find default address, or fallback to first one
        $address = $customer->addresses->where('is_default', true)->first()
            ?? $customer->addresses->first();

        return [
            $customer->customer_code,
            $customer->name,
            $customer->email,
            $customer->mobile,
            $customer->outstanding_balance,
            $customer->credit_limit,
            $customer->created_at->format('Y-m-d'),
            $customer->is_active ? 'Active' : 'Inactive',
            // Address
            $address?->address_line1 ?? '',
            $address?->address_line2 ?? '',
            $address?->village ?? '',
            $address?->taluka ?? '',
            $address?->district ?? '',
            $address?->state ?? '',
            $address?->pincode ?? '',
            $address?->country ?? '',
        ];
    }
}
