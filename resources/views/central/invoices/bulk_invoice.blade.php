<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Invoice Print</title>
    
    <style>
        @page { size: A4; margin: 25px; }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 10.8px;
            color: #111;
            line-height: 1.35;
        }

        .page-break {
            page-break-after: always;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }

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

        th, td {
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

        .muted { color: #444; }

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

@foreach($invoices as $index => $invoice)
    <div class="invoice-container {{ !$loop->last ? 'page-break' : '' }}">
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
                        <tr><td class="label">Name</td><td>{{ $invoice->order->customer->first_name ?? '' }} {{ $invoice->order->customer->last_name ?? '' }}</td></tr>
                        <tr><td class="label">Mobile</td><td>{{ $invoice->order->customer->mobile ?? 'N/A' }}</td></tr>

                        @if($invoice->order->billingAddress)
                            <tr><td class="label">Address</td><td>{{ $invoice->order->billingAddress->address_line1 }}</td></tr>
                            @if($invoice->order->billingAddress->address_line2)
                                <tr><td class="label"></td><td>{{ $invoice->order->billingAddress->address_line2 }}</td></tr>
                            @endif
                            <tr><td class="label">Village</td><td>{{ $invoice->order->billingAddress->village }}</td></tr>
                            <tr><td class="label">Taluka</td><td>{{ $invoice->order->billingAddress->taluka ?? '-' }}</td></tr>
                            <tr><td class="label">District</td><td>{{ $invoice->order->billingAddress->district ?? '-' }}</td></tr>
                            <tr><td class="label">Post Office</td><td>{{ $invoice->order->billingAddress->post_office ?? '-' }}</td></tr>
                            <tr><td class="label">State / PIN</td><td>{{ $invoice->order->billingAddress->state }} - {{ $invoice->order->billingAddress->pincode }}</td></tr>
                            <tr><td class="label">Country</td><td>{{ $invoice->order->billingAddress->country ?? 'India' }}</td></tr>
                        @else
                            <tr><td colspan="2">N/A</td></tr>
                        @endif
                    </table>
                </td>

                <!-- Shipping -->
                <td>
                    <table class="no-border">
                        <tr><td class="label">Name</td><td>{{ $invoice->order->customer->first_name ?? '' }} {{ $invoice->order->customer->last_name ?? '' }}</td></tr>
                        <tr><td class="label">Mobile</td><td>{{ $invoice->order->customer->mobile ?? 'N/A' }}</td></tr>

                        @if($invoice->order->shippingAddress)
                            <tr><td class="label">Address</td><td>{{ $invoice->order->shippingAddress->address_line1 }}</td></tr>
                            @if($invoice->order->shippingAddress->address_line2)
                                <tr><td class="label"></td><td>{{ $invoice->order->shippingAddress->address_line2 }}</td></tr>
                            @endif
                            <tr><td class="label">Village</td><td>{{ $invoice->order->shippingAddress->village }}</td></tr>
                            <tr><td class="label">Taluka</td><td>{{ $invoice->order->shippingAddress->taluka ?? '-' }}</td></tr>
                            <tr><td class="label">District</td><td>{{ $invoice->order->shippingAddress->district ?? '-' }}</td></tr>
                            <tr><td class="label">Post Office</td><td>{{ $invoice->order->shippingAddress->post_office ?? '-' }}</td></tr>
                            <tr><td class="label">State / PIN</td><td>{{ $invoice->order->shippingAddress->state }} - {{ $invoice->order->shippingAddress->pincode }}</td></tr>
                            <tr><td class="label">Country</td><td>{{ $invoice->order->shippingAddress->country ?? 'India' }}</td></tr>
                        @else
                            <tr><td colspan="2">Same as Billing</td></tr>
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
                    <th width="5%">Sl</th>
                    <th width="40%">Description of Goods</th>
                    <th width="10%">Qty</th>
                    <th width="15%">Unit Cost</th>
                    <th width="10%">Disc.</th>
                    <th width="20%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->order->items as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $item->product_name }}<br><small class="muted">{{ $item->sku }}</small></td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <br>

        <!-- ================= TOTALS ================= -->
        <table class="totals">
            <tr>
                <td colspan="5" class="text-right bold">Subtotal</td>
                <td class="text-right">{{ number_format($invoice->order->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right bold">Discount on All</td>
                <td class="text-right">{{ number_format($invoice->order->discount_amount, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td colspan="5" class="text-right">Grand Total</td>
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
    </div>
@endforeach

</body>
</html>
