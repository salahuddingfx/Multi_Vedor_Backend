<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Tax Invoice - {{ $order->tracking_id }}</title>
    <style>
        @page {
            size: A4;
            margin: 0.75in;
        }
        body {
            font-family: 'Inter', 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
            font-size: 11px; /* Image uses small clean font */
            line-height: 1.5;
            background: #e2e8f0;
        }

        /* Site Specific Themes */
        .theme-acharu { --primary: #800000; --primary-light: #fef2f2; }
        .theme-tajashutki { --primary: #064e3b; --primary-light: #f0fdf4; }
        .theme-unspecified { --primary: #fbbf24; --primary-light: #fef3c7; }

        .invoice-wrapper {
            margin-bottom: 50px; /* Space for the fixed footer in DomPDF */
        }

        @media screen {
            body { padding: 40px 0; }
            .invoice-wrapper {
                width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                background: #ffffff;
                box-shadow: 0 20px 50px rgba(0,0,0,0.1);
                box-sizing: border-box;
                padding: 0.75in;
                position: relative;
            }
            .print-btn {
                position: fixed; bottom: 30px; right: 30px;
                background: #1e293b; color: #fff; padding: 12px 25px;
                border-radius: 50px; font-weight: 700; cursor: pointer; border: none;
                z-index: 100; box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            }
            .print-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }
            
            .screen-only-footer { 
                position: absolute;
                bottom: 0.75in;
                left: 0.75in;
                right: 0.75in;
                width: calc(100% - 1.5in);
            }
        }

        @media print {
            .print-btn { display: none; }
            .invoice-wrapper { width: 100%; height: 100%; box-shadow: none; border: none; margin-bottom: 50px; }
            body { background: #fff; }
        }

        .invoice-wrapper {
            position: relative;
        }

        /* PAID Watermark */
        .paid-stamp {
            position: absolute;
            top: 30%;
            left: 50%;
            border: 8px double #10b981;
            color: #10b981;
            font-size: 80px;
            font-weight: 900;
            padding: 10px 40px;
            transform: translate(-50%, -50%) rotate(-15deg);
            opacity: 0.15;
            text-transform: uppercase;
            letter-spacing: 10px;
            z-index: 0;
            pointer-events: none;
        }

        /* Top Header */
        .header-top {
            display: block;
            margin-bottom: 25px;
        }
        .logo-box {
            display: inline-block;
        }
        .logo-title {
            font-size: 26px;
            font-weight: 900;
            color: #1e293b;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .logo-tagline {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 1px;
            margin-top: 2px;
            font-weight: 700;
        }

        /* Accent Bar Area */
        .accent-bar-area {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .bar-left {
            display: table-cell;
            background: var(--primary);
            height: 35px;
            width: 55%;
        }
        .invoice-title-cell {
            display: table-cell;
            padding: 0 20px;
            vertical-align: middle;
            white-space: nowrap;
            width: 1%;
        }
        .invoice-title {
            font-size: 38px;
            font-weight: 600;
            color: #334155;
            letter-spacing: 1px;
            margin: 0;
            line-height: 1;
        }
        .bar-right {
            display: table-cell;
            background: var(--primary);
            height: 35px;
            width: 10%; /* Fills remainder */
        }

        /* Info Section */
        .info-section {
            width: 100%;
            margin-bottom: 40px;
        }
        .info-left {
            width: 50%;
            vertical-align: top;
        }
        .info-right {
            width: 50%;
            vertical-align: top;
        }

        .invoice-to-label {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .recipient-name {
            font-size: 16px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .recipient-address {
            font-size: 11px;
            color: #475569;
            font-weight: 600;
            line-height: 1.5;
        }

        .meta-table {
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 0 4px 20px;
        }
        .meta-label {
            font-weight: 700;
            color: #1e293b;
            font-size: 13px;
        }
        .meta-value {
            font-weight: 600;
            color: #475569;
            text-align: right;
            font-size: 12px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .items-table th {
            background-color: #334155;
            color: #ffffff;
            padding: 12px 15px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
        }
        .items-table th.right { text-align: right; }
        .items-table th.center { text-align: center; }
        
        .items-table td {
            padding: 15px;
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            font-weight: 600;
            font-size: 11px;
        }
        /* Alternating row colors */
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .items-table td.right { text-align: right; }
        .items-table td.center { text-align: center; }
        .items-table td.index { width: 50px; text-align: center; }

        /* Footer Section */
        .footer-grid {
            width: 100%;
            margin-bottom: 60px;
        }
        .footer-left {
            width: 55%;
            vertical-align: top;
            padding-right: 20px;
        }
        .footer-right {
            width: 45%;
            vertical-align: top;
        }

        .thank-you-text {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }
        .terms-title, .payment-title {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
            margin-top: 15px;
        }
        .terms-text {
            font-size: 9px;
            color: #475569;
            line-height: 1.5;
            font-weight: 500;
            width: 80%;
        }

        .payment-info-table {
            border-collapse: collapse;
        }
        .payment-info-table td {
            padding: 2px 0;
            font-size: 9px;
        }
        .payment-label {
            font-weight: 600;
            color: #1e293b;
            padding-right: 15px;
        }
        .payment-val {
            font-weight: 500;
            color: #475569;
            text-transform: capitalize;
        }

        /* Totals Table */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 15px;
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
        }
        .totals-table .val {
            text-align: right;
            font-weight: 600;
        }
        
        .total-highlight td {
            background-color: var(--primary);
            color: #fff;
            font-size: 14px !important;
            padding: 12px 15px !important;
        }
        .total-highlight .val {
            color: #fff;
            font-weight: 700;
        }

        /* Footer logic */
        .print-only-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            width: 100%;
            display: block;
        }
        .screen-only-footer {
            display: block;
        }
        .bottom-left {
            width: 60%;
            vertical-align: bottom;
        }
        .bottom-bar {
            background: var(--primary);
            height: 4px;
            width: 100%;
            margin-bottom: 10px;
        }
        .bottom-contact {
            font-size: 10px;
            font-weight: 700;
            color: #1e293b;
        }
        
        .bottom-right {
            width: 40%;
            vertical-align: bottom;
            text-align: right;
        }
        .auth-sign-line {
            border-top: 1px solid #1e293b;
            width: 150px;
            display: inline-block;
            margin-bottom: 8px;
        }
        .auth-sign-text {
            font-size: 10px;
            font-weight: 700;
            color: #1e293b;
        }

    </style>
</head>
<body class="theme-{{ $order->site?->slug ?? 'unspecified' }}">
    <button class="print-btn" onclick="window.print()">Print Invoice</button>

    @if(isset($is_pdf) && $is_pdf)
    <footer class="print-only-footer">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
            <td class="bottom-left">
                <div class="bottom-bar"></div>
                <div class="bottom-contact">
                    {{ data_get($order->site, 'settings.support_phone') ?? data_get($order->site, 'settings.contact') ?? ($order->site?->slug === 'acharu' ? '01700000000' : '01800000000') }} &nbsp;|&nbsp; 
                    {{ data_get($order->site, 'settings.address') ?? ($order->site?->slug === 'acharu' ? 'Dhaka, Bangladesh' : 'Cox\'s Bazar, Bangladesh') }} &nbsp;|&nbsp; 
                    {{ data_get($order->site, 'settings.website') ?? ($order->site?->slug === 'acharu' ? 'www.acharu.com' : 'www.tajashutki.com') }}
                </div>
            </td>
            <td class="bottom-right">
                <div class="auth-sign-line"></div><br>
                <span class="auth-sign-text">Authorised Sign</span>
            </td>
            </tr>
        </table>
    </footer>
    @endif

    <main class="invoice-wrapper">

        @if($order->payment_status === 'paid')
        <div class="paid-stamp">PAID</div>
        @endif

        <div class="header-top">
            <div class="logo-box">
                <h1 class="logo-title">{{ $order->site?->name ?? 'ACHARU' }}</h1>
                <div class="logo-tagline">{{ $order->site?->slug === 'acharu' ? 'Premium Artisanal Collection' : 'Freshness Delivered Daily' }}</div>
            </div>
        </div>

        <table style="width: 100%; margin-bottom: 30px; border-collapse: collapse;">
            <tr>
                <td style="background: var(--primary); height: 35px; width: 55%;"></td>
                <td style="padding: 0 20px; vertical-align: middle; white-space: nowrap; width: 1%;">
                    <h2 class="invoice-title">INVOICE</h2>
                </td>
                <td style="background: var(--primary); height: 35px;"></td>
            </tr>
        </table>

        <table class="info-section">
            <tr>
            <td class="info-left">
                <div class="invoice-to-label">Invoice to:</div>
                <div class="recipient-name">{{ $order->customer_name }}</div>
                <div class="recipient-address">
                    {{ $order->customer_phone }}<br>
                    {{ $order->customer_address }}<br>
                    {{ $order->location }}
                </div>
                
                @if($order->customer_notes)
                <div style="margin-top: 15px; padding: 10px; background-color: #fef3c7; border-left: 3px solid #f59e0b; color: #b45309; font-size: 11px; font-weight: 600;">
                    <span style="font-weight: 800; display: block; margin-bottom: 2px;">Customer Note:</span>
                    {{ $order->customer_notes }}
                </div>
                @endif
            </td>
            <td class="info-right">
                <table align="right" class="meta-table" style="margin-bottom: 20px;">
                    <tr>
                        <td class="meta-label">Invoice#</td>
                        <td class="meta-value">{{ strtoupper($order->tracking_id) }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Date</td>
                        <td class="meta-value">{{ $order->created_at->format('d / m / Y') }}</td>
                    </tr>
                </table>
                <div style="clear: both;"></div>

                <div style="float: right; text-align: right; margin-top: 10px;">
                    <div class="invoice-to-label" style="font-size: 13px;">Invoice From:</div>
                    <div class="recipient-name" style="font-size: 14px;">{{ data_get($order->site, 'settings.store_name') ?? $order->site?->name ?? 'Acharu' }}</div>
                    <div class="recipient-address">
                        {{ data_get($order->site, 'settings.address') ?? ($order->site?->slug === 'acharu' ? 'Dhaka, Bangladesh' : 'Cox\'s Bazar, Bangladesh') }}<br>
                        {{ data_get($order->site, 'settings.support_phone') ?? data_get($order->site, 'settings.contact') ?? ($order->site?->slug === 'acharu' ? '01700000000' : '01800000000') }}<br>
                        {{ data_get($order->site, 'settings.store_email') ?? 'support@' . ($order->site?->slug === 'acharu' ? 'acharu.com' : 'tajashutki.com') }}
                    </div>
                </div>
            </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="index">SL.</th>
                    <th>Item Description</th>
                    <th class="center">Price</th>
                    <th class="center">Qty.</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td class="index">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->name }} @if($item->variation_info) ({{ $item->variation_info }}) @endif
                    </td>
                    <td class="center">৳{{ number_format($item->price, 2) }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">৳{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="footer-grid">
            <tr>
            <td class="footer-left">
                <div class="thank-you-text">Thank you for shopping with us</div>
                
                <div class="terms-title">Terms & Conditions</div>
                <div class="terms-text">
                    Please check your items upon delivery. For any concerns regarding quality or packaging, contact our support team with your Invoice ID.
                </div>

                <div class="payment-title">Order Info:</div>
                <table class="payment-info-table">
                    <tr>
                        <td class="payment-label">Status:</td>
                        <td class="payment-val">{{ $order->status }}</td>
                    </tr>
                    <tr>
                        <td class="payment-label">Payment Mode:</td>
                        <td class="payment-val">{{ $order->payment_method }}</td>
                    </tr>
                    <tr>
                        <td class="payment-label">Payment Status:</td>
                        <td class="payment-val">{{ $order->payment_status }}</td>
                    </tr>
                    @if($order->customer_notes)
                    <tr>
                        <td class="payment-label">Note:</td>
                        <td class="payment-val">Included in Customer Details</td>
                    </tr>
                    @endif
                </table>
            </td>
            
            <td class="footer-right">
                <table class="totals-table">
                    <tr>
                        <td>Sub Total:</td>
                        <td class="val">৳{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Delivery Charge:</td>
                        <td class="val">৳{{ number_format($order->delivery_charge, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                    <tr>
                        <td>Discount:</td>
                        <td class="val">-৳{{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-highlight">
                        <td>Total:</td>
                        <td class="val">৳{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
            </tr>
        </table>

        @if(!isset($is_pdf) || !$is_pdf)
        <div style="height: 100px;"></div> {{-- Spacer to prevent footer overlap on screen --}}
        <!-- Footer for Screen Preview -->
        <div class="screen-only-footer">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                <td class="bottom-left">
                    <div class="bottom-bar"></div>
                    <div class="bottom-contact">
                        {{ data_get($order->site, 'settings.support_phone') ?? data_get($order->site, 'settings.contact') ?? ($order->site?->slug === 'acharu' ? '01700000000' : '01800000000') }} &nbsp;|&nbsp; 
                        {{ data_get($order->site, 'settings.address') ?? ($order->site?->slug === 'acharu' ? 'Dhaka, Bangladesh' : 'Cox\'s Bazar, Bangladesh') }} &nbsp;|&nbsp; 
                        {{ data_get($order->site, 'settings.website') ?? ($order->site?->slug === 'acharu' ? 'www.acharu.com' : 'www.tajashutki.com') }}
                    </div>
                </td>
                <td class="bottom-right">
                    <div class="auth-sign-line"></div><br>
                    <span class="auth-sign-text">Authorised Sign</span>
                </td>
                </tr>
            </table>
        </div>
        @endif

    </main>
</body>
</html>
