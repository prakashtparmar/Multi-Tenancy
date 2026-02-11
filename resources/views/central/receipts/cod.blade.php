<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>COD Receipt - {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 12px;
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
        }

        .label-container {
            border: 2px solid #000;
            padding: 12px;
            max-width: 500px;
            /* Optional: adjust based on need */
            margin: 0 auto;
        }

        .row {
            display: table;
            width: 100%;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .box {
            border: 1px solid #000;
            padding: 8px;
            margin-top: 8px;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .big {
            font-size: 16px;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .muted {
            font-size: 10px;
            color: #333;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        .items-table th {
            background: #f2f2f2;
        }
    </style>
</head>

<body>

    <div class="label-container">

        <div class="row">
            <div class="col big">
                Pincode: {{ $order->shippingAddress->pincode ?? '-' }}
            </div>
            <div class="col right big">
                COD Amount: Rs. {{ number_format($order->grand_total, 2) }}
            </div>
        </div>

        <div class="center title" style="margin-top:8px;">
            BUSINESS PARCEL<br>
            CASH ON DELIVERY (COD)
        </div>

        <div class="center muted" style="margin-top:4px;">
            Payment Office : Rajkot H.O. <br>
            Register No / E-Biller ID : {{ $order->order_number }}<br>
            Order Date: {{ $order->created_at->format('d-m-Y H:i') }}
        </div>

        <div class="divider"></div>

        <div class="box">
            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">To (Consignee),</div>
            <strong>Name:</strong> {{ $order->customer->name }}<br>
            <strong>Address:</strong> {{ $order->shippingAddress->address_line1 }}<br>
            @if($order->shippingAddress->village) <strong>Village:</strong> {{ $order->shippingAddress->village }},
            @endif
            @if($order->shippingAddress->taluka) <strong>Taluka:</strong> {{ $order->shippingAddress->taluka }}, @endif
            <strong>District:</strong> {{ $order->shippingAddress->district }}<br>
            <strong>Post Office:</strong> {{ $order->shippingAddress->post_office }}<br>
            <strong>State:</strong> {{ $order->shippingAddress->state }} - {{ $order->shippingAddress->pincode }}<br>
            <strong>Contact:</strong> {{ $order->customer->mobile }}
        </div>

        <div class="box">
            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">From (Sender),</div>
            <strong>Krushify Agro Pvt. Ltd.</strong><br>
            Srp Camp, New 150ft Ring Road, Ghanteshwar,<br>
            Bapa Sitaram Chowk, Vardhman Sheri Block No: 22<br>
            Gujarat. | <strong>Mobile:</strong> 9199125925<br>
            <strong>GST:</strong> 24AAMCK0386L1Z6
        </div>



        <div class="divider"></div>

        <div class="muted center">
            If article undelivered, please arrange return to <strong>Rajkot H.O.</strong><br>
            <em>“I hereby certify that this article does not contain any dangerous or prohibited goods according to
                Indian Post rules.”</em>
        </div>

        <div style="margin-top: 20px;" class="center">
            ___________________________<br>
            <span class="muted">Receiver Signature</span>
        </div>

    </div>

</body>

</html>