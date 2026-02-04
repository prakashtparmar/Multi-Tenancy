<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>

    <style>
        @page { margin: 40px 25px; }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .clear { clear: both; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }

        /* Header */
        .header {
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-info { width: 60%; float: left; }
        .invoice-info { width: 40%; float: right; text-align: right; }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Addresses */
        .addresses {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
        }

        .address-box { width: 48%; float: left; }
        .address-box-right { width: 48%; float: right; }

        .address-header {
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            margin-bottom: 5px;
            display: block;
        }

        /* Items */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            background: #f2f2f2;
            text-align: center;
        }

        td.desc { text-align: left; }

        /* Totals */
        .total-box {
            width: 300px;
            float: right;
            margin-top: 10px;
        }

        .grand-total {
            font-weight: bold;
            background: #eee;
        }

        /* Footer */
        .terms {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 11px;
        }

        .footer-note {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>

{{-- ================= HEADER ================= --}}
<div class="header">
    <div class="company-info">
        <div class="company-name">Krushify</div>
        <div>
            <strong>Address:</strong> The One World (B), 1005, Ayodhya Circle<br>
            <strong>Mobile:</strong> 9199125925<br>
            <strong>Email:</strong> info@krushifyagro.com<br>
            <strong>GST:</strong> 24AAMCK0386L1Z6
        </div>
    </div>

    <div class="invoice-info">
        <div class="invoice-title">Invoice</div>
        <p><strong>No:</strong> {{ $invoice->invoice_number }}</p>
        <p><strong>Date:</strong> {{ $invoice->issue_date->format('d-m-Y') }}</p>
    </div>

    <div class="clear"></div>
</div>

{{-- ================= ADDRESSES ================= --}}
<div class="addresses">
    <div class="address-box">
        <span class="address-header">Billing Address</span>
        <strong>
            {{ $invoice->order->customer->first_name ?? '' }}
            {{ $invoice->order->customer->last_name ?? '' }}
        </strong><br>

        @if($invoice->order->billingAddress)
            {{ $invoice->order->billingAddress->address_line1 }}<br>
            @if($invoice->order->billingAddress->address_line2)
                {{ $invoice->order->billingAddress->address_line2 }}<br>
            @endif
            {{ $invoice->order->billingAddress->village }},
            {{ $invoice->order->billingAddress->state }} -
            {{ $invoice->order->billingAddress->pincode }}
        @else
            N/A
        @endif
    </div>

    <div class="address-box-right">
        <span class="address-header">Shipping Address</span>
        @if($invoice->order->shippingAddress)
            {{ $invoice->order->shippingAddress->address_line1 }}<br>
            {{ $invoice->order->shippingAddress->village }},
            {{ $invoice->order->shippingAddress->state }} -
            {{ $invoice->order->shippingAddress->pincode }}
        @else
            Same as Billing
        @endif
    </div>

    <div class="clear"></div>
</div>

{{-- ================= ITEMS ================= --}}
<table>
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="35%">Description</th>
            <th width="10%">Qty</th>
            <th width="15%">Unit Price</th>
            <th width="15%">Discount</th>
            <th width="20%">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->order->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="desc">
                    {{ $item->product_name }}<br>
                    <small>{{ $item->sku }}</small>
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ================= TOTALS ================= --}}
<div class="total-box">
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ number_format($invoice->order->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="text-right">{{ number_format($invoice->order->discount_amount, 2) }}</td>
        </tr>
        <tr class="grand-total">
            <td>Grand Total</td>
            <td class="text-right">{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
    </table>
</div>

<div class="clear"></div>

{{-- ================= TERMS ================= --}}
<div class="terms">
    <strong>Terms & Conditions</strong>
    <ol>
        <li>Goods once sold will not be taken back.</li>
        <li>Late payments may attract interest.</li>
        <li>Subject to local jurisdiction.</li>
        <li>This is a computer-generated invoice.</li>
    </ol>
</div>

<div class="footer-note">
    Thank you for your business!
</div>

</body>
</html>
