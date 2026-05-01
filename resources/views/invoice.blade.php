<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->tracking_id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
        }

        .invoice-container {
            width: 8.5in;
            min-height: 11in;
            padding: 0.5in;
            margin: auto;
            box-sizing: border-box;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 32px;
            font-weight: 900;
            margin: 0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .logo p {
            font-size: 12px;
            margin: 5px 0 0;
            color: #666;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-meta h2 {
            font-size: 20px;
            font-weight: 900;
            margin: 0;
            color: #000;
        }

        .invoice-meta p {
            font-size: 12px;
            margin: 5px 0;
            font-weight: bold;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .info-block h4 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .info-block p {
            font-size: 13px;
            margin: 3px 0;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #888;
            border-bottom: 2px solid #000;
            padding: 10px 0;
        }

        td {
            padding: 15px 0;
            font-size: 13px;
            border-bottom: 1px solid #eee;
        }

        .item-name {
            font-weight: bold;
        }

        .item-weight {
            font-size: 11px;
            color: #888;
        }

        .totals {
            width: 300px;
            margin-left: auto;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 13px;
        }

        .total-row.grand-total {
            margin-top: 10px;
            padding: 15px;
            background-color: #f9f9f9;
            font-size: 18px;
            font-weight: 900;
            border-top: 2px solid #000;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 11px;
            color: #999;
            font-style: italic;
        }

        @media print {
            body { background: none; }
            .invoice-container { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none; }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="no-print print-btn" onclick="window.print()">Print Invoice</button>

    <div class="invoice-container">
        <div class="header">
            <div class="logo">
                <h1>{{ $order->site_id == 1 ? 'ACHARU' : 'TAJA SHUTKI' }}</h1>
                <p>Artisanal Quality Delivered</p>
            </div>
            <div class="invoice-meta">
                <h2>INVOICE</h2>
                <p>#{{ $order->tracking_id }}</p>
                <span>{{ $order->created_at->format('M d, Y') }}</span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-block">
                <h4>Bill To:</h4>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p>{{ $order->customer_phone }}</p>
                <p>{{ $order->customer_address }}</p>
                <p>{{ $order->location }}</p>
            </div>
            <div class="info-block" style="text-align: right;">
                <h4>Order Summary:</h4>
                <p>Status: <strong>{{ strtoupper($order->status) }}</strong></p>
                <p>Payment: <strong>{{ strtoupper($order->payment_method) }}</strong></p>
                <p>Payment Status: <strong>{{ strtoupper($order->payment_status) }}</strong></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        <div class="item-weight">{{ $item->weight }}kg</div>
                    </td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">৳{{ number_format($item->price, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">৳{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>৳{{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Delivery Charge:</span>
                <span>৳{{ number_format($order->delivery_charge, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>৳{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for choosing artisanal excellence.</p>
            <p style="font-size: 9px; margin-top: 10px;">This is a computer generated invoice. No signature required.</p>
        </div>
    </div>

    <script>
        // Auto-open print dialog
        window.onload = function() {
            // window.print();
        }
    </script>
</body>
</html>
