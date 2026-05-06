<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->tracking_id }} | {{ $order->site_id == 1 ? 'Acharu' : 'Taja Shutki' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $order->site_id == 1 ? '#800000' : '#064e3b' }};
            --slate-900: #0f172a;
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
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: var(--slate-900);
        }

        .page-container {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            margin: 0 auto;
            position: relative;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 8px solid var(--primary-color);
            padding-bottom: 30px;
            margin-bottom: 40px;
        }

        .header-left {
            display: table-cell;
            vertical-align: bottom;
        }

        .header-right {
            display: table-cell;
            vertical-align: bottom;
            text-align: right;
        }

        .brand h1 {
            font-size: 42px;
            font-weight: 900;
            margin: 0;
            color: var(--primary-color);
            letter-spacing: -2px;
        }

        .brand p {
            margin: 5px 0 0;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--slate-400);
            font-weight: 700;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: 900;
            color: var(--slate-900);
            margin: 0;
        }

        .invoice-id {
            display: inline-block;
            background: var(--slate-100);
            padding: 6px 15px;
            border-radius: 8px;
            margin-top: 10px;
            color: var(--primary-color);
            font-weight: 900;
            font-size: 16px;
        }

        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }

        .recipient-box {
            display: table-cell;
            width: 60%;
            background: #f8fafc;
            padding: 25px;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
        }

        .summary-box {
            display: table-cell;
            width: 40%;
            padding: 0 0 0 40px;
            vertical-align: top;
        }

        .section-label {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: block;
        }

        .recipient-name {
            font-size: 18px;
            font-weight: 900;
            margin: 0 0 5px;
        }

        .recipient-phone {
            font-size: 15px;
            font-weight: 700;
            color: var(--slate-600);
            margin-bottom: 10px;
        }

        .recipient-address {
            font-size: 13px;
            color: var(--slate-600);
            line-height: 1.5;
            margin: 0;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border-bottom: 1px solid var(--slate-100);
            padding-bottom: 8px;
        }

        .summary-label {
            display: table-cell;
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-400);
        }

        .summary-value {
            display: table-cell;
            text-align: right;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            margin-bottom: 40px;
        }

        th {
            text-align: left;
            padding: 10px 20px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--slate-400);
        }

        .item-row td {
            padding: 15px 20px;
            background: #fff;
            border: 1px solid var(--slate-100);
            font-size: 14px;
        }

        .item-row td:first-child { border-radius: 12px 0 0 12px; border-right: none; }
        .item-row td:nth-child(2) { border-left: none; border-right: none; text-align: center; }
        .item-row td:last-child { border-radius: 0 12px 12px 0; border-left: none; text-align: right; font-weight: 900; }

        .item-name { font-weight: 800; font-size: 15px; }
        .item-meta { font-size: 11px; color: var(--primary-color); font-weight: 700; margin-top: 3px; }

        .totals-section {
            display: table;
            width: 100%;
        }

        .note-box {
            display: table-cell;
            width: 55%;
            background: #fdf2f2;
            padding: 20px;
            border-radius: 20px;
            border: 1px dashed var(--primary-color);
            vertical-align: top;
        }

        .totals-wrap {
            display: table-cell;
            width: 45%;
            padding-left: 40px;
        }

        .totals-box {
            background: var(--slate-900);
            padding: 25px;
            border-radius: 25px;
            color: #fff;
        }

        .bill-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            font-size: 13px;
            opacity: 0.7;
        }

        .bill-label { display: table-cell; }
        .bill-val { display: table-cell; text-align: right; }

        .grand-total-row {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .grand-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 900;
            opacity: 0.5;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .grand-price {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -1px;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            border-top: 1px solid var(--slate-100);
            padding-top: 30px;
        }

        .footer p { font-size: 12px; font-weight: 700; margin: 4px 0; }
        .footer .credits { font-size: 9px; text-transform: uppercase; color: var(--slate-400); letter-spacing: 1px; margin-top: 15px; }

        .no-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 900;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <a href="javascript:window.print()" class="no-print">Print Invoice</a>

    <div class="page-container">
        <div class="header">
            <div class="header-left">
                <div class="brand">
                    <h1>{{ $order->site_id == 1 ? 'ACHARU' : 'TAJA SHUTKI' }}</h1>
                    <p>Premium Artisanal Quality</p>
                </div>
            </div>
            <div class="header-right">
                <h2 class="invoice-title">TAX INVOICE</h2>
                <div class="invoice-id">#{{ $order->tracking_id }}</div>
                <p style="margin: 8px 0 0; font-size: 12px; color: var(--slate-400); font-weight: 700;">{{ $order->created_at->format('d F, Y') }}</p>
            </div>
        </div>

        <div class="info-section">
            <div class="recipient-box">
                <span class="section-label">Bill To</span>
                <p class="recipient-name">{{ $order->customer_name }}</p>
                <p class="recipient-phone">{{ $order->customer_phone }}</p>
                <p class="recipient-address">{{ $order->customer_address }}, {{ $order->location }}</p>
            </div>
            <div class="summary-box">
                <span class="section-label">Logistics</span>
                <div class="summary-row">
                    <span class="summary-label">Status:</span>
                    <span class="summary-value">{{ $order->status }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Payment:</span>
                    <span class="summary-value">{{ $order->payment_method }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Weight:</span>
                    <span class="summary-value">{{ $order->total_weight }} KG</span>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr class="item-row">
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        <div class="item-meta">Rate: ৳{{ number_format($item->price) }}</div>
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>৳{{ number_format($item->price * $item->quantity) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="note-box">
                <h5 style="margin: 0 0 10px; font-size: 11px; font-weight: 900; color: var(--primary-color); text-transform: uppercase;">Customer Note:</h5>
                <p style="margin: 0; font-size: 11px; color: var(--slate-600); line-height: 1.5; font-weight: 500;">
                    Please inspect your delivery items immediately. For any issues, contact our support team within 24 hours with this Invoice ID.
                </p>
            </div>
            <div class="totals-wrap">
                <div class="totals-box">
                    <div class="bill-row">
                        <span class="bill-label">Subtotal</span>
                        <span class="bill-val">৳{{ number_format($order->subtotal) }}</span>
                    </div>
                    <div class="bill-row">
                        <span class="bill-label">Delivery</span>
                        <span class="bill-val">৳{{ number_format($order->delivery_charge) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="bill-row" style="color: #fb923c; opacity: 1;">
                        <span class="bill-label">Discount</span>
                        <span class="bill-val">-৳{{ number_format($order->discount_amount) }}</span>
                    </div>
                    @endif
                    <div class="grand-total-row">
                        <p class="grand-label">Total Payable Amount</p>
                        <div class="grand-price">৳{{ number_format($order->total_amount) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your patronage!</p>
            <p style="text-transform: uppercase; letter-spacing: 1px; font-size: 10px; color: var(--slate-400);">Authentic Flavors • Artisanal Craft • Pure Heritage</p>
            <div class="credits">Computer Generated Invoice • No Signature Required</div>
        </div>
    </div>
</body>
</html>

