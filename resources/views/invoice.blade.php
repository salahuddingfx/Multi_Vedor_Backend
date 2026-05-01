<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->tracking_id }} | {{ $order->site_id == 1 ? 'Acharu' : 'Taja Shutki' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $order->site_id == 1 ? '#7c2d12' : '#065f46' }}; /* Maroon for Acharu, Green for Taja */
            --slate-800: #1e293b;
            --slate-600: #475569;
            --slate-400: #94a3b8;
            --slate-100: #f1f5f9;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: var(--slate-800);
        }

        .page-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        /* Decorative Background */
        .page-container::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            opacity: 0.03;
            border-radius: 50%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 50px;
            position: relative;
        }

        .brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            margin: 0;
            color: var(--primary-color);
            letter-spacing: -1px;
        }

        .brand p {
            margin: 5px 0 0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--slate-400);
            font-weight: 600;
        }

        .invoice-label {
            text-align: right;
        }

        .invoice-label h2 {
            font-size: 48px;
            font-weight: 800;
            margin: 0;
            color: var(--slate-100);
            line-height: 1;
            position: absolute;
            right: 0;
            top: -10px;
            z-index: 0;
        }

        .invoice-label .meta {
            position: relative;
            z-index: 1;
            padding-top: 15px;
        }

        .meta p {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .meta span {
            color: var(--slate-400);
            font-size: 12px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: block;
            border-bottom: 2px solid var(--slate-100);
            padding-bottom: 8px;
        }

        .address-block h3 {
            margin: 0 0 8px;
            font-size: 18px;
            font-weight: 600;
        }

        .address-block p {
            margin: 4px 0;
            font-size: 13px;
            color: var(--slate-600);
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th {
            text-align: left;
            background: var(--slate-100);
            padding: 15px 20px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--slate-600);
        }

        td {
            padding: 20px;
            border-bottom: 1px solid var(--slate-100);
            font-size: 14px;
        }

        .item-info h4 {
            margin: 0 0 5px;
            font-size: 15px;
            font-weight: 600;
        }

        .item-info span {
            font-size: 12px;
            color: var(--slate-400);
        }

        .totals-container {
            display: flex;
            justify-content: flex-end;
        }

        .totals-box {
            width: 320px;
            background: var(--slate-100);
            padding: 30px;
            border-radius: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
            color: var(--slate-600);
        }

        .total-row.grand-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed var(--slate-400);
            color: var(--slate-800);
            font-weight: 800;
            font-size: 22px;
        }

        .total-row.grand-total span:last-child {
            color: var(--primary-color);
        }

        .footer {
            margin-top: 80px;
            text-align: center;
            border-top: 1px solid var(--slate-100);
            padding-top: 30px;
        }

        .footer p {
            font-size: 13px;
            color: var(--slate-400);
            margin: 5px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
            background: {{ $order->payment_status == 'paid' ? '#ecfdf5' : '#fff1f2' }};
            color: {{ $order->payment_status == 'paid' ? '#059669' : '#e11d48' }};
        }

        @media print {
            body { background: white; }
            .page-container { margin: 0; box-shadow: none; }
            .no-print { display: none; }
        }

        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Outfit', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .print-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <button class="no-print print-btn" onclick="window.print()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Print Invoice
    </button>

    <div class="page-container">
        <div class="header">
            <div class="brand">
                <h1>{{ $order->site_id == 1 ? 'Acharu' : 'Taja Shutki' }}</h1>
                <p>Premium Homemade Quality</p>
            </div>
            <div class="invoice-label">
                <h2>INVOICE</h2>
                <div class="meta">
                    <p>#{{ $order->tracking_id }}</p>
                    <span>{{ $order->created_at->format('M d, Y | h:i A') }}</span>
                    <br>
                    <div class="status-badge">{{ $order->payment_status }}</div>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="address-block">
                <span class="section-title">Customer Details</span>
                <h3>{{ $order->customer_name }}</h3>
                <p>{{ $order->customer_phone }}</p>
                <p>{{ $order->customer_address }}</p>
                <p><strong>Region:</strong> {{ $order->location == 'Cox' ? "Cox's Bazar" : 'Outside District' }}</p>
            </div>
            <div class="address-block" style="text-align: right;">
                <span class="section-title">Payment Info</span>
                <p><strong>Method:</strong> {{ strtoupper($order->payment_method) }}</p>
                @if($order->transaction_id)
                    <p><strong>Trx ID:</strong> {{ $order->transaction_id }}</p>
                @endif
                @if($order->sender_number)
                    <p><strong>Sender:</strong> {{ $order->sender_number }}</p>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Rate</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td class="item-info">
                        <h4>{{ $item->name }}</h4>
                        <span>Weight: {{ $item->weight }}kg</span>
                    </td>
                    <td style="text-align: center; font-weight: 600;">{{ $item->quantity }}</td>
                    <td style="text-align: right; color: var(--slate-600);">৳{{ number_format($item->price, 2) }}</td>
                    <td style="text-align: right; font-weight: 600;">৳{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-container">
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Delivery Charge</span>
                    <span>৳{{ number_format($order->delivery_charge, 2) }}</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total Amount</span>
                    <span>৳{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for choosing {{ $order->site_id == 1 ? 'Acharu' : 'Taja Shutki' }}!</p>
            <p>For any queries, please contact us with your Tracking ID.</p>
            <p style="font-size: 10px; margin-top: 20px;">© {{ date('Y') }} Multi-Store Management System. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
