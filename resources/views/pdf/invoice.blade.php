<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Tax Invoice - {{ $order->tracking_id }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Inter', 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
            background: #f8fafc;
        }

        /* Site Specific Themes */
        .theme-acharu { --primary: #800000; --primary-light: #fef2f2; --accent: #1e293b; }
        .theme-taja-shutki { --primary: #064e3b; --primary-light: #f0fdf4; --accent: #1e293b; }
        .theme-unspecified { --primary: #1e293b; --primary-light: #f1f5f9; --accent: #1e293b; }

        @media screen {
            body { background: #e2e8f0; padding: 40px 0; }
            .invoice-wrapper {
                width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                background: #ffffff;
                padding: 0.75in;
                box-shadow: 0 20px 50px rgba(0,0,0,0.1);
                box-sizing: border-box;
                position: relative;
            }
            .print-btn {
                position: fixed; bottom: 30px; right: 30px;
                background: #1e293b; color: #fff; padding: 12px 25px;
                border-radius: 50px; font-weight: 700; cursor: pointer; border: none;
                z-index: 100; box-shadow: 0 10px 20px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
            }
            .print-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }
        }

        @media print {
            .print-btn { display: none; }
            .invoice-wrapper { width: 100%; height: 100%; padding: 0.5in; box-shadow: none; }
            body { background: #fff; }
        }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 8px solid var(--primary); padding-bottom: 30px; }
        .header-left { float: left; width: 60%; }
        .header-right { float: right; width: 40%; text-align: right; }
        .clear { clear: both; }

        .logo { font-size: 42px; font-weight: 900; color: var(--primary); letter-spacing: -2px; line-height: 1; text-transform: uppercase; }
        .tagline { font-size: 14px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 2px; margin-top: 8px; }
        
        .invoice-title { font-size: 24px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; }
        .tracking-box { 
            display: inline-block; background: #f1f5f9; color: var(--primary); 
            padding: 8px 16px; border-radius: 8px; font-weight: 900; 
            font-size: 14px; margin-top: 12px; margin-bottom: 8px;
        }
        .date-text { font-size: 12px; color: #94a3b8; font-weight: 700; }

        .info-grid { margin-bottom: 50px; display: grid; grid-template-columns: 1.5fr 1fr; gap: 60px; }
        .recipient-box { 
            float: left; width: 55%; background: #f8fafc; border-radius: 20px; padding: 30px;
            border: 1px solid #f1f5f9; min-height: 160px;
        }
        .summary-box { float: right; width: 38%; padding-top: 5px; }

        .section-label { font-size: 11px; font-weight: 900; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display: block; opacity: 0.8; }
        .recipient-name { font-size: 20px; font-weight: 900; color: #0f172a; margin-bottom: 6px; }
        .recipient-info { font-size: 14px; color: #475569; font-weight: 600; line-height: 1.6; }

        .summary-item { margin-bottom: 10px; clear: both; font-size: 13px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; }
        .summary-label { float: left; color: #64748b; font-weight: 700; }
        .summary-val { float: right; color: #0f172a; font-weight: 900; text-transform: uppercase; }

        .items-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-bottom: 40px; }
        .items-table th { 
            text-align: left; font-size: 11px; font-weight: 900; color: #94a3b8; 
            text-transform: uppercase; letter-spacing: 1px; padding: 0 20px 10px;
        }
        .items-table td { background: #fff; border: 1px solid #f1f5f9; padding: 20px; }
        .items-table td:first-child { border-radius: 15px 0 0 15px; border-right: none; }
        .items-table td:last-child { border-radius: 0 15px 15px 0; border-left: none; text-align: right; }
        .items-table td:nth-child(2) { border-left: none; border-right: none; text-align: center; }

        .product-name { font-size: 15px; font-weight: 900; color: #1e293b; margin-bottom: 4px; }
        .product-meta { font-size: 12px; color: var(--primary); font-weight: 700; }
        .qty-badge { background: #f8fafc; padding: 6px 12px; border-radius: 8px; font-weight: 900; color: #1e293b; display: inline-block; }
        .line-total { font-size: 16px; font-weight: 900; color: #0f172a; }

        .bottom-section { margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .note-container { 
            float: left; width: 50%; background: var(--primary-light); border-radius: 20px; padding: 25px;
            border: 1px dashed var(--primary); opacity: 0.9;
        }
        .total-container { 
            float: right; width: 42%; background: #0f172a; border-radius: 25px; padding: 30px;
            color: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .total-row { margin-bottom: 12px; clear: both; font-size: 14px; opacity: 0.7; }
        .total-label { float: left; font-weight: 600; }
        .total-val { float: right; font-weight: 700; }
        
        .grand-total-row { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; margin-top: 20px; clear: both; }
        .grand-total-label { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; opacity: 0.6; margin-bottom: 5px; display: block; }
        .grand-total-val { font-size: 32px; font-weight: 900; letter-spacing: -1px; float: left; }
        .vat-badge { float: right; font-size: 12px; font-weight: 700; opacity: 0.4; margin-top: 15px; }

        .footer { 
            position: absolute; bottom: 40px; left: 0; right: 0; text-align: center; padding: 0 0.75in;
            border-top: 2px solid #f1f5f9; paddingTop: 30px;
        }
        .footer-thank { font-size: 16px; font-weight: 900; color: #1e293b; margin-bottom: 8px; }
        .footer-tagline { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; }
        .footer-legal { font-size: 9px; color: #cbd5e1; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }

        /* PAID Watermark */
        .paid-stamp {
            position: absolute;
            top: 220px;
            right: 80px;
            border: 8px double #10b981;
            border-radius: 20px;
            color: #10b981;
            font-size: 60px;
            font-weight: 900;
            padding: 10px 40px;
            transform: rotate(-15deg);
            opacity: 0.15;
            text-transform: uppercase;
            letter-spacing: 10px;
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="theme-{{ $order->site?->slug ?? 'unspecified' }}">
    <button class="print-btn" onclick="window.print()">Print Invoice</button>

    <div class="invoice-wrapper">
        @if($order->payment_status === 'paid')
        <div class="paid-stamp">PAID</div>
        @endif

        <div class="header">
            <div class="header-left">
                <div class="logo">{{ $order->site?->name ?? 'ACHARU' }}</div>
                <div class="tagline">{{ $order->site?->slug === 'acharu' ? 'PREMIUM ARTISANAL COLLECTION' : 'FRESHNESS DELIVERED DAILY' }}</div>
            </div>
            <div class="header-right">
                <div class="invoice-title">Tax Invoice</div>
                <div class="tracking-box">#{{ strtoupper($order->tracking_id) }}</div>
                <div class="date-text">{{ $order->created_at->format('F d, Y') }}</div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="info-grid">
            <div class="recipient-box">
                <span class="section-label">Billed To</span>
                <div class="recipient-name">{{ $order->customer_name }}</div>
                <div class="recipient-info">
                    {{ $order->phone }}<br>
                    {{ $order->address }}
                </div>
                @if($order->customer_notes)
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                    <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; color: var(--primary); opacity: 0.6;">Note:</span>
                    <div style="font-size: 12px; font-weight: 700; color: #1e293b;">{{ $order->customer_notes }}</div>
                </div>
                @endif
            </div>
            <div class="summary-box">
                <span class="section-label">Order Details</span>
                <div class="summary-item">
                    <span class="summary-label">Status</span>
                    <span class="summary-val">{{ $order->status }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Payment</span>
                    <span class="summary-val">{{ $order->payment_method }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label" style="color: {{ $order->payment_status === 'unpaid' ? '#b91c1c' : '#059669' }};">Payment Status</span>
                    <span class="summary-val" style="color: {{ $order->payment_status === 'unpaid' ? '#b91c1c' : '#059669' }};">{{ $order->payment_status }}</span>
                </div>
                <div class="summary-item" style="border: none;">
                    <span class="summary-label">Total Weight</span>
                    <span class="summary-val">{{ number_format($order->items->sum(fn($i) => ($i->product?->weight ?? 0) * $i->quantity), 2) }} KG</span>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="60%">Description</th>
                    <th width="15%">Quantity</th>
                    <th width="25%">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->name }} @if($item->variation_info) ({{ $item->variation_info }}) @endif</div>
                        <div class="product-meta">Unit Price: ৳{{ number_format($item->price, 0) }}</div>
                    </td>
                    <td>
                        <span class="qty-badge">{{ $item->quantity }}</span>
                    </td>
                    <td>
                        <span class="line-total">৳{{ number_format($item->price * $item->quantity, 0) }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="bottom-section">
            <div class="note-container">
                <span class="section-label" style="color: var(--primary); margin-bottom: 10px;">Customer Notice</span>
                <p style="font-size: 12px; color: #475569; font-weight: 600; line-height: 1.6; margin: 0;">
                    Please check your items upon delivery. For any concerns regarding quality or packaging, contact our support team with your Invoice ID.
                </p>
            </div>
            <div class="total-container">
                <div class="total-row">
                    <span class="total-label">Subtotal</span>
                    <span class="total-val">৳{{ number_format($order->subtotal, 0) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Delivery Charge</span>
                    <span class="total-val">৳{{ number_format($order->delivery_charge, 0) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="total-row" style="color: #fda4af; opacity: 1;">
                    <span class="total-label">Discount</span>
                    <span class="total-val">-৳{{ number_format($order->discount_amount, 0) }}</span>
                </div>
                @endif
                <div class="grand-total-row">
                    <span class="grand-total-label">{{ $order->payment_status === 'paid' ? 'Total Amount Paid' : 'Total Payable Amount' }}</span>
                    <span class="grand-total-val">৳{{ number_format($order->total_amount, 2) }}</span>
                    <span class="vat-badge">Inc. VAT</span>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="footer">
            <div class="footer-thank">Thank you for your patronage!</div>
            <div class="footer-tagline">Authentic Flavors • Artisanal Craft • Pure Heritage</div>
            <div class="footer-legal">Computer Generated Invoice • No Signature Required</div>
        </div>
    </div>
</body>
</html>
