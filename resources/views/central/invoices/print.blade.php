<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        @page { margin: 100px 25px; }
        body { font-family: 'Helvetica', Arial, sans-serif; color: #333; line-height: 1.4; font-size: 13px; }
        .header { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .logo { width: 50%; float: left; font-size: 24px; font-weight: bold; }
        .invoice-details { width: 50%; float: right; text-align: right; }
        .invoice-details h1 { margin: 0; font-size: 28px; color: #444; }
        .invoice-details p { margin: 2px 0; }
        .clear { clear: both; }
        
        .addresses { width: 100%; margin-bottom: 30px; margin-top: 20px; }
        .address-box { width: 48%; float: left; }
        .address-box-right { width: 48%; float: right; }
        .address-box h3 { margin-bottom: 5px; border-bottom: 1px solid #ddd; padding-bottom: 5px; font-size: 14px; }
        .address-box p { margin: 0; }
        
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.items th { background-color: #f8f9fa; font-weight: bold; border-bottom: 2px solid #ddd; }
        table.items th, table.items td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        .text-right { text-align: right; }
        
        .summary-container { width: 100%; margin-top: 20px; }
        .total-section { float: right; width: 250px; }
        .total-table { width: 100%; border-collapse: collapse; }
        .total-table td { padding: 5px 0; }
        .grand-total { font-weight: bold; font-size: 1.2em; color: #000; border-top: 2px solid #333; }
        
        .footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; color: #777; font-size: 0.8em; border-top: 1px solid #eee; padding-top: 10px; }
        .print-btn { text-align: right; margin-bottom: 20px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            {{ config('app.name', 'Central Admin') }}
        </div>
        <div class="invoice-details">
            <h1>INVOICE</h1>
            <p><strong>Invoice No:</strong> #INV-{{ $order->id }}</p>
            <p><strong>Order No:</strong> {{ $order->order_number }}</p>
            @if($order->shipping_status === 'shipped' && $order->shipments->isNotEmpty())
                <p><strong>Tracking No:</strong> {{ $order->shipments->first()->tracking_number }}</p>
            @endif
            <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="addresses">
        <div class="address-box">
            <h3>Bill To:</h3>
            <p>
                <strong>{{ $order->customer->first_name }} {{ $order->customer->last_name }}</strong><br>
                @if($order->customer->company_name){{ $order->customer->company_name }}<br>@endif
                {{ $order->customer->email }}<br>
                {{ $order->customer->mobile }}
            </p>
            @if($order->billingAddress)
                <p style="margin-top: 5px;">
                    {{ $order->billingAddress->address_line1 }}<br>
                    @if($order->billingAddress->address_line2){{ $order->billingAddress->address_line2 }}<br>@endif
                    {{ $order->billingAddress->district }}, {{ $order->billingAddress->state }} - {{ $order->billingAddress->pincode }}
                </p>
            @endif
        </div>
        <div class="address-box-right">
            <h3>Ship To:</h3>
            <p>
                @if($order->shippingAddress)
                    <strong>{{ $order->shippingAddress->contact_name ?? $order->customer->name }}</strong><br>
                    {{ $order->shippingAddress->address_line1 }}<br>
                    @if($order->shippingAddress->address_line2){{ $order->shippingAddress->address_line2 }}<br>@endif
                    {{ $order->shippingAddress->district }}, {{ $order->shippingAddress->state }} - {{ $order->shippingAddress->pincode }}<br>
                    Phone: {{ $order->shippingAddress->contact_phone ?? $order->customer->mobile }}
                @else
                    <strong>{{ $order->customer->first_name }} {{ $order->customer->last_name }}</strong><br>
                    @if($order->customer->company_name){{ $order->customer->company_name }}<br>@endif
                    {{ $order->customer->email }}<br>
                    {{ $order->customer->mobile }}
                @endif
            </p>
        </div>
        <div class="clear"></div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->product_name ?? $item->product->name ?? 'Unknown Item' }}</strong><br>
                    <small style="color: #666;">SKU: {{ $item->sku ?? $item->product->sku ?? 'N/A' }}</small>
                </td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">
                    @if($item->discount_amount > 0)
                        -{{ number_format($item->discount_amount, 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">{{ number_format(($item->quantity * $item->unit_price) - $item->discount_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-container">
        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">{{ number_format($order->total_amount, 2) }}</td>
                </tr>
                @php
                    $itemDiscounts = $order->items->sum('discount_amount');
                    $orderLevelDiscount = $order->discount_amount - $itemDiscounts;
                @endphp
                @if($itemDiscounts > 0)
                <tr>
                    <td>Item Discounts:</td>
                    <td class="text-right">-{{ number_format($itemDiscounts, 2) }}</td>
                </tr>
                @endif
                @if($orderLevelDiscount > 0)
                <tr>
                    <td>Order Discount:</td>
                    <td class="text-right">-{{ number_format($orderLevelDiscount, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td style="padding-top: 10px;">Total (Rs):</td>
                    <td class="text-right" style="padding-top: 10px;">{{ number_format($order->grand_total, 2) }}</td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>

    <div class="footer">
        <p>Thank you for your business! | Payment Status: {{ ucfirst($order->payment_status) }}</p>
        <p>This is a computer generated invoice.</p>
    </div>
</body>
</html>
