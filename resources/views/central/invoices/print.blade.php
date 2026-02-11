<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Delivery Challan - {{ $invoice->invoice_number }}</title>

    <style>
        @page {
            size: A4;
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 10.8px;
            color: #111;
            line-height: 1.35;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            padding: 6px;
            border: 2px solid #000;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #f1f1f1;
            font-weight: bold;
        }

        .no-border td {
            border: none;
            padding: 2px 4px;
        }

        .label {
            width: 38%;
            font-weight: bold;
            white-space: nowrap;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
        }

        .muted {
            color: #444;
        }

        .items th {
            text-align: center;
            background: #eaeaea;
        }

        .items td {
            padding: 6px 5px;
        }

        .totals td {
            padding: 6px;
        }

        .grand-total {
            font-size: 13px;
            font-weight: bold;
            background: #efefef;
        }

        .terms {
            font-size: 10px;
            line-height: 1.35;
        }
    </style>
</head>

<body>

    <div class="title">DELIVERY CHALLAN</div>

    <!-- ================= HEADER ================= -->
    <table>
        <tr>
            <td width="60%">
                <div class="company-name">Krushify</div>
                <div class="muted">
                    <strong>Mobile:</strong> 9199125925<br>
                    <strong>Address:</strong> The One World (B), 1005, Ayodhya Circle<br>
                    <strong>Email:</strong> info@krushifyagro.com<br>
                    <strong>GST:</strong> 24AAMCK0386L1Z6
                </div>
            </td>
            <td width="40%">
                <strong>Invoice No:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Dated:</strong> {{ $invoice->issue_date->format('d-m-Y') }}<br><br>
                <strong>Reference No.</strong><br>
                Seed Lic No.: GAN/FSR220001380/2022-2023<br>
                Pesti Lic No.: GAN/FP1220002020/2022-2023
            </td>
        </tr>
    </table>

    <br>

    <!-- ================= ADDRESSES ================= -->
    <table>
        <tr>
            <th width="50%" align="left">Customer Address</th>
            <th width="50%" align="left">Shipping Address</th>
        </tr>

        <tr>
            <!-- Billing -->
            <td>
                <table class="no-border">
                    <tr>
                        <td class="label">Name</td>
                        <td>{{ $invoice->order->customer->first_name ?? '' }}
                            {{ $invoice->order->customer->last_name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Mobile</td>
                        <td>{{ $invoice->order->customer->mobile ?? 'N/A' }}</td>
                    </tr>

                    @if($invoice->order->billingAddress)
                        <tr>
                            <td class="label">Address</td>
                            <td>{{ $invoice->order->billingAddress->address_line1 }}</td>
                        </tr>
                        @if($invoice->order->billingAddress->address_line2)
                            <tr>
                                <td class="label"></td>
                                <td>{{ $invoice->order->billingAddress->address_line2 }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label">Village</td>
                            <td>{{ $invoice->order->billingAddress->village }}</td>
                        </tr>
                        <tr>
                            <td class="label">Taluka</td>
                            <td>{{ $invoice->order->billingAddress->taluka ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">District</td>
                            <td>{{ $invoice->order->billingAddress->district ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Post Office</td>
                            <td>{{ $invoice->order->billingAddress->post_office ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">State / PIN</td>
                            <td>{{ $invoice->order->billingAddress->state }} -
                                {{ $invoice->order->billingAddress->pincode }}</td>
                        </tr>
                        <tr>
                            <td class="label">Country</td>
                            <td>{{ $invoice->order->billingAddress->country ?? 'India' }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="2">N/A</td>
                        </tr>
                    @endif
                </table>
            </td>

            <!-- Shipping -->
            <td>
                <table class="no-border">
                    <tr>
                        <td class="label">Name</td>
                        <td>{{ $invoice->order->customer->first_name ?? '' }}
                            {{ $invoice->order->customer->last_name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Mobile</td>
                        <td>{{ $invoice->order->customer->mobile ?? 'N/A' }}</td>
                    </tr>

                    @if($invoice->order->shippingAddress)
                        <tr>
                            <td class="label">Address</td>
                            <td>{{ $invoice->order->shippingAddress->address_line1 }}</td>
                        </tr>
                        @if($invoice->order->shippingAddress->address_line2)
                            <tr>
                                <td class="label"></td>
                                <td>{{ $invoice->order->shippingAddress->address_line2 }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label">Village</td>
                            <td>{{ $invoice->order->shippingAddress->village }}</td>
                        </tr>
                        <tr>
                            <td class="label">Taluka</td>
                            <td>{{ $invoice->order->shippingAddress->taluka ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">District</td>
                            <td>{{ $invoice->order->shippingAddress->district ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Post Office</td>
                            <td>{{ $invoice->order->shippingAddress->post_office ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">State / PIN</td>
                            <td>{{ $invoice->order->shippingAddress->state }} -
                                {{ $invoice->order->shippingAddress->pincode }}</td>
                        </tr>
                        <tr>
                            <td class="label">Country</td>
                            <td>{{ $invoice->order->shippingAddress->country ?? 'India' }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="2">Same as Billing</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <br>

    <!-- ================= ITEMS ================= -->
    <table class="items">
        <thead>
            <tr>
                <th width="4%">Sl</th>
                <th width="30%">Description</th>
                <th width="6%">Qty</th>
                <th width="10%">Rate</th>
                <th width="8%">Disc.</th>
                <th width="12%">Taxable</th>
                <th width="10%">CGST</th>
                <th width="10%">SGST</th>
                <th width="10%">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTaxable = 0;
                $totalCGST = 0;
                $totalSGST = 0;
            @endphp
            @foreach($invoice->order->items as $i => $item)
                @php
                    $baseTotal = $item->unit_price * $item->quantity;
                    $discount = $item->discount_amount ?? 0;
                    
                    // Tax calculation logic matching controller (tax on base price currently, or implied on taxable?)
                    // The previous view logic was: ($baseTotal * rate) / 100.
                    // We will stick to that to preserve values, but display 'Taxable' as (Base - Disc).
                    // WARNING: If Tax is on Base, then Taxable Value displayed might be misleading if we claim Tax is calc'd on it.
                    // However, for standard Indian GST, it's usually on Transaction Value (Price - Disc).
                    // The Controller actually calculated tax on Price * Qty (Base).
                    // We will display "Net Amt" (Base - Disc) as Taxable, but keep Tax Amount derived from Controller/View logic to avoid 'impacting other logic'.
                    // Actually, let's verify if we can just split the existing `taxAmount` safely.
                    
                    $taxAmount = ($baseTotal * ($item->tax_percent ?? 0)) / 100;
                    $taxableValue = $baseTotal - $discount; // For display purpose
                    
                    $cgstRate = ($item->tax_percent ?? 0) / 2;
                    $sgstRate = ($item->tax_percent ?? 0) / 2;
                    $cgstAmount = $taxAmount / 2;
                    $sgstAmount = $taxAmount / 2;
                    
                    $lineTotal = $baseTotal + $taxAmount - $discount;
                    
                    $totalTaxable += $taxableValue;
                    $totalCGST += $cgstAmount;
                    $totalSGST += $sgstAmount;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->product_name }}<br><small class="muted">{{ $item->sku }}</small></td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($discount, 2) }}</td>
                    <td class="text-right">{{ number_format($taxableValue, 2) }}</td>
                    
                    <td class="text-right">
                        @if($cgstRate > 0)
                            <span style="font-size:9px;">{{ $cgstRate }}%</span><br>
                            {{ number_format($cgstAmount, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if($sgstRate > 0)
                            <span style="font-size:9px;">{{ $sgstRate }}%</span><br>
                            {{ number_format($sgstAmount, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    
                    <td class="text-right">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <!-- ================= TOTALS ================= -->
    <table class="totals">
        <tr>
            <td colspan="8" class="text-right bold">Total Taxable Value</td>
            <td class="text-right">{{ number_format($totalTaxable, 2) }}</td>
        </tr>
        <tr>
            <td colspan="8" class="text-right bold">Total CGST</td>
            <td class="text-right">{{ number_format($totalCGST, 2) }}</td>
        </tr>
        <tr>
            <td colspan="8" class="text-right bold">Total SGST</td>
            <td class="text-right">{{ number_format($totalSGST, 2) }}</td>
        </tr>
        <tr class="grand-total">
            <td colspan="8" class="text-right">Grand Total</td>
            <td class="text-right">{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
    </table>

    <br>

    <!-- ================= TERMS ================= -->
    <table class="terms">
        <tr>
            <td>
                <strong>Terms & Conditions</strong><br>
                1. Composition taxable person, not eligible to collect tax on supplies.<br>
                2. Goods once sold will not be taken back.<br>
                3. Subject to local jurisdiction.<br>
                4. This is a computer-generated invoice.
            </td>
        </tr>
    </table>

</body>

</html>