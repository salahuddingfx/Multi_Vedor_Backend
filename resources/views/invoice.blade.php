<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->tracking_id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #000;
            line-height: 1.2;
        }

        .receipt-container {
            width: 1.5in;
            padding: 0.1in;
            margin: 0;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 14px;
            margin: 0;
            text-transform: uppercase;
        }

        .header p {
            font-size: 8px;
            margin: 2px 0 0;
        }

        .info-block {
            font-size: 8px;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .info-block p {
            margin: 1px 0;
        }

        .items-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8px;
        }

        .item-row {
            margin-bottom: 5px;
        }

        .item-header {
            font-weight: bold;
            font-size: 8px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 7px;
        }

        .totals {
            border-top: 1px dashed #000;
            padding-top: 5px;
            font-size: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .total-row.grand-total {
            font-size: 10px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 3px;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        @media print {
            @page {
                size: 1.5in auto;
                margin: 0;
            }
            body { background: none; }
            .no-print { display: none; }
            .receipt-container { width: 1.5in; padding: 0.1in; }
        }

        .print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            background: #000;
            color: #fff;
            border: none;
            font-size: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <button class="no-print print-btn" onclick="window.print()">Print</button>

    <div class="receipt-container">
        <div class="header">
            <h1>{{ $order->site_id == 1 ? 'ACHARU' : 'TAJA SHUTKI' }}</h1>
            <p>Order: #{{ substr($order->tracking_id, -6) }}</p>
            <p>{{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="info-block">
            <p><strong>To: {{ $order->customer_name }}</strong></p>
            <p>{{ $order->customer_phone }}</p>
            <p style="font-size: 7px;">{{ $order->customer_address }}</p>
        </div>

        <div class="items-table">
            @foreach($order->items as $item)
            <div class="item-row">
                <div class="item-header">{{ $item->quantity }}x {{ $item->name }}</div>
                <div class="item-details">
                    <span>@ ৳{{ number_format($item->price, 0) }}</span>
                    <span>৳{{ number_format($item->price * $item->quantity, 0) }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>৳{{ number_format($order->subtotal, 0) }}</span>
            </div>
            <div class="total-row">
                <span>Delivery:</span>
                <span>৳{{ number_format($order->delivery_charge, 0) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>৳{{ number_format($order->total_amount, 0) }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Payment: {{ strtoupper($order->payment_method) }}</p>
            <p><strong>{{ strtoupper($order->payment_status) }}</strong></p>
            <p style="margin-top: 10px;">Thank you for your order!</p>
        </div>
    </div>
</body>
</html>
