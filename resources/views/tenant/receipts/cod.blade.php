<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COD Receipt - {{ $order->order_number }}</title>
    <style>
        body { font-family: 'Courier', monospace; width: 72mm; margin: 0; padding: 5px; font-size: 11px; color: #000; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .separator { border-top: 1px dashed #000; margin: 5px 0; }
        .row-table { width: 100%; border-collapse: collapse; }
        .row-table td { padding: 2px 0; vertical-align: top; }
        .text-right { text-align: right; }
        .item-row { margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="center">
        <h2 style="margin:0; font-size: 16px;">{{ tenant('id') ? ucfirst(tenant('id')) : config('app.name') }}</h2>
        <p style="margin:2px 0;">CASH ON DELIVERY RECEIPT</p>
    </div>

    <div class="separator"></div>
    
    <table class="row-table">
        <tr>
            <td>Order:</td>
            <td class="text-right bold">{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td class="text-right">{{ $order->created_at->format('d/m/y H:i') }}</td>
        </tr>
        <tr>
            <td>Customer:</td>
            <td class="text-right">{{ $order->customer->name }}</td>
        </tr>
    </table>

    <div class="separator"></div>

    <div class="bold" style="margin-bottom: 5px;">ITEMS</div>
    @foreach($order->items as $item)
    <div class="item-row">
        <div class="bold">{{ $item->product_name ?? $item->product->name ?? 'Unknown' }}</div>
        <table class="row-table">
            <tr>
                <td>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format(($item->quantity * $item->unit_price) - $item->discount_amount, 2) }}</td>
            </tr>
        </table>
    </div>
    @endforeach

    <div class="separator"></div>

    <table class="row-table" style="font-size: 14px;">
        <tr class="bold">
            <td>TOTAL TO PAY:</td>
            <td class="text-right">Rs {{ number_format($order->grand_total, 2) }}</td>
        </tr>
    </table>

    <div class="separator"></div>
    
    <div class="center" style="margin-top: 15px;">
        <p>Customer Signature</p>
        <br><br>
        <p>____________________</p>
        <p style="margin-top: 10px;">Thank you for your order!</p>
    </div>
</body>
</html>
