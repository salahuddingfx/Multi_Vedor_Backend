<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Tax Invoice - {{ $order->tracking_id }}</title>
    <style>
        @page {
            size: A4;
            margin: 0.3in;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #2D3748;
            font-size: 13px;
            line-height: 1.5;
            background: #ffffff;
            position: relative;
        }

        /* Site Specific Themes */
        .theme-acharu { --primary: #800000; --primary-light: #fff5f5; --accent: #1A202C; }
        .theme-taja-shutki { --primary: #006400; --primary-light: #f0fff4; --accent: #1A202C; }

        @media screen {
            body { background: #E2E8F0; padding: 40px 0; }
            .invoice-wrapper {
                width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                background: #ffffff;
                padding: 0.5in;
                padding-bottom: 50mm;
                box-shadow: 0 20px 50px rgba(0,0,0,0.1);
                box-sizing: border-box;
                position: relative;
            }
            .print-btn {
                position: fixed; bottom: 30px; right: 30px;
                background: #2D3748; color: #fff; padding: 12px 25px;
                border-radius: 50px; font-weight: 700; cursor: pointer; border: none;
                z-index: 100; box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            }
        }

        @media print {
            .print-btn { display: none; }
            .invoice-wrapper { box-shadow: none; padding: 0.3in; padding-bottom: 40mm; min-height: 100%; }
            body { background: #fff; }
        }

        .header { margin-bottom: 20px; }
        .header-left { float: left; width: 60%; }
        .header-right { float: right; width: 35%; text-align: right; }
        .clear { clear: both; }

        .logo { font-size: 42px; font-weight: 900; color: var(--primary); letter-spacing: -2px; line-height: 1; }
        .tagline { font-size: 12px; font-weight: 800; color: #718096; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }
        
        .invoice-title { font-size: 28px; font-weight: 900; color: #1A202C; text-transform: uppercase; }
        .tracking-badge { 
            display: inline-block; background: var(--primary); color: #fff; 
            padding: 6px 15px; border-radius: 50px; font-weight: 900; 
            font-size: 14px; margin-top: 8px; margin-bottom: 5px;
        }
        .date-text { font-size: 12px; color: #A0AEC0; font-weight: 700; }

        .header-bar { border-bottom: 8px solid var(--primary); margin: 15px 0 30px 0; }

        .info-row { margin-bottom: 40px; }
        .recipient-box { 
            float: left; width: 55%; background: #F8FAFC; border-radius: 25px; padding: 25px;
            border: 1px solid #EDF2F7; min-height: 180px;
        }
        .summary-list { float: right; width: 35%; padding-top: 10px; }

        .section-label { font-size: 10px; font-weight: 900; color: var(--primary); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; display: block; }
        .recipient-name { font-size: 22px; font-weight: 900; color: #1A202C; margin-bottom: 5px; }
        .recipient-info { font-size: 14px; color: #4A5568; font-weight: 600; line-height: 1.8; }

        .summary-item { margin-bottom: 12px; clear: both; font-size: 13px; }
        .summary-label { float: left; color: #718096; font-weight: 700; }
        .summary-val { float: right; color: #1A202C; font-weight: 800; text-align: right; }

        .items-header { 
            border-bottom: 1px solid #EDF2F7; padding-bottom: 10px; margin-bottom: 20px;
            font-size: 10px; font-weight: 800; color: #A0AEC0; text-transform: uppercase; letter-spacing: 2px;
        }
        .item-col-details { float: left; width: 60%; }
        .item-col-qty { float: left; width: 15%; text-align: center; }
        .item-col-total { float: right; width: 25%; text-align: right; }

        .product-row { padding: 25px 0; border-bottom: 1px solid #F8FAFC; clear: both; page-break-inside: avoid; }
        .product-card { 
            background: #fff; border: 1px solid #EDF2F7; border-radius: 20px; padding: 20px; 
            margin-bottom: 15px; position: relative;
        }
        .product-name { font-size: 16px; font-weight: 900; color: #1A202C; margin-bottom: 4px; }
        .product-price-sm { font-size: 12px; color: var(--primary); font-weight: 700; }
        .product-qty-val { font-size: 20px; font-weight: 900; color: #1A202C; margin-top: 5px; display: block; }
        .product-total-val { font-size: 22px; font-weight: 900; color: #1A202C; }

        .bottom-row { margin-top: 25px; clear: both; page-break-inside: avoid; }
        .note-box { 
            float: left; width: 55%; border: 1px dashed #FED7D7; border-radius: 15px; padding: 15px;
            background: #FFF5F5; min-height: 100px;
        }
        .total-box { 
            float: right; width: 40%; background: #0F172A; border-radius: 20px; padding: 20px;
            color: #fff; min-height: 100px;
        }

        .total-line { margin-bottom: 8px; clear: both; font-size: 13px; opacity: 0.8; }
        .total-label-light { float: left; font-weight: 600; }
        .total-val-light { float: right; font-weight: 700; }
        
        .grand-total-row { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 5px; clear: both; }
        .grand-total-label { float: left; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; }
        .grand-total-val { float: right; font-size: 26px; font-weight: 900; }
        .vat-text { font-size: 10px; opacity: 0.5; text-align: right; clear: both; margin-top: 2px; }

        .footer { 
            position: absolute; bottom: 20px; left: 0; right: 0; text-align: center;
        }
        .footer-thank { font-size: 16px; font-weight: 900; color: #1A202C; margin-bottom: 5px; }
        .footer-tagline { font-size: 10px; font-weight: 800; color: #A0AEC0; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; }
        .footer-legal { font-size: 9px; color: #CBD5E0; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body class="theme-{{ $order->site?->slug }}">
    @media screen
    <button class="print-btn" onclick="window.print()">Print Invoice</button>
    @endmedia

    <div class="invoice-wrapper">
        <div class="header">
            <div class="header-left">
                <div class="logo">{{ $order->site?->name ?? 'ACHARU' }}</div>
                <div class="tagline">{{ $order->site?->slug === 'acharu' ? 'PREMIUM ARTISANAL COLLECTION' : 'FRESHNESS DELIVERED DAILY' }}</div>
            </div>
            <div class="header-right">
                <div class="invoice-title">TAX INVOICE</div>
                <div class="tracking-badge">#{{ strtoupper($order->tracking_id) }}</div>
                <div class="date-text">{{ $order->created_at->format('M d, Y') }}</div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="header-bar"></div>

        <div class="info-row">
            <div class="recipient-box">
                <span class="section-label">Recipient Information</span>
                <div class="recipient-name">{{ $order->customer_name }}</div>
                <div class="recipient-info">
                    {{ $order->phone }}<br>
                    {{ $order->address }}
                </div>
            </div>
            <div class="summary-list">
                <span class="section-label">Order Summary</span>
                <div class="summary-item">
                    <span class="summary-label">Order Status:</span>
                    <span class="summary-val" style="text-transform: uppercase;">{{ $order->status }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Payment Method:</span>
                    <span class="summary-val" style="text-transform: uppercase;">{{ $order->payment_method }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label" style="color: {{ $order->payment_status === 'unpaid' ? '#800000' : '#059669' }};">Payment Status:</span>
                    <span class="summary-val" style="text-transform: uppercase; color: {{ $order->payment_status === 'unpaid' ? '#800000' : '#059669' }};">{{ $order->payment_status }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Weight:</span>
                    <span class="summary-val">{{ number_format($order->items->sum(fn($i) => $i->product?->weight * $i->quantity), 2) }} KG</span>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="items-header">
            <div class="item-col-details">Item Details</div>
            <div class="item-col-qty">Quantity</div>
            <div class="item-col-total">Line Total</div>
            <div class="clear"></div>
        </div>

        <div class="products-list">
            @foreach($order->items as $item)
            <div class="product-card">
                <div class="item-col-details">
                    <div class="product-name">{{ $item->name }} @if($item->variation_info) ({{ $item->variation_info }}) @endif</div>
                    <div class="product-price-sm">Unit Price: ৳{{ number_format($item->price, 0) }}</div>
                </div>
                <div class="item-col-qty">
                    <span class="product-qty-val">{{ $item->quantity }}</span>
                </div>
                <div class="item-col-total">
                    <span class="product-total-val">৳{{ number_format($item->price * $item->quantity, 0) }}</span>
                </div>
                <div class="clear"></div>
            </div>
            @endforeach
        </div>

        <div class="bottom-row">
            <div class="note-box">
                <span class="section-label">Note to Customer:</span>
                <p style="font-size: 12px; color: #718096; font-weight: 600; line-height: 1.6;">
                    {{ $order->customer_notes ?: 'Please check your items upon delivery. For any concerns regarding quality or packaging, contact our support team with your Invoice ID.' }}
                </p>
            </div>
            <div class="total-box">
                <div class="total-line">
                    <span class="total-label-light">Subtotal</span>
                    <span class="total-val-light">৳{{ number_format($order->subtotal, 0) }}</span>
                </div>
                <div class="total-line">
                    <span class="total-label-light">Delivery Charge</span>
                    <span class="total-val-light">৳{{ number_format($order->delivery_charge, 0) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="total-line" style="color: #feb2b2;">
                    <span class="total-label-light">Discount</span>
                    <span class="total-val-light">-৳{{ number_format($order->discount_amount, 0) }}</span>
                </div>
                @endif
                <div class="grand-total-row">
                    <span class="grand-total-label">Total Payable Amount</span>
                    <span class="grand-total-val">৳{{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="vat-text">Inc. VAT</div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="footer">
            <div class="footer-thank">Thank you for your patronage!</div>
            <div class="footer-tagline">AUTHENTIC FLAVORS • ARTISANAL CRAFT • PURE HERITAGE</div>
            <div class="footer-legal">COMPUTER GENERATED INVOICE • NO SIGNATURE REQUIRED</div>
        </div>
    </div>
</body>
</html>
