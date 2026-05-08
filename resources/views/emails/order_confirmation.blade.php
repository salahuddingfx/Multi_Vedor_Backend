<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
        }

        .header {
            padding: 48px 40px;
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        /* Site Specific Branding */
        .header.site-1 { background: linear-gradient(135deg, #800000 0%, #4a0000 100%); } /* Acharu */
        .header.site-2 { background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); } /* TajaShutki */

        .logo {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(4px);
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .content {
            padding: 40px;
        }

        h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .order-card {
            background: #f1f5f9;
            border-radius: 20px;
            padding: 24px;
            margin: 32px 0;
        }

        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .item-name { font-weight: 600; color: #334155; }
        .item-price { font-weight: 700; color: #0f172a; }

        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 16px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-top: 16px;
        }

        .total-label { font-size: 14px; font-weight: 600; color: #64748b; }
        .total-value { font-size: 28px; font-weight: 800; color: #0f172a; }

        .button {
            display: block;
            text-align: center;
            padding: 18px 32px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 800;
            font-size: 16px;
            margin-top: 32px;
            transition: transform 0.2s;
        }

        .button.site-1 { background: #800000; color: #ffffff; }
        .button.site-2 { background: #064e3b; color: #ffffff; }

        .address-box {
            border-left: 4px solid #e2e8f0;
            padding-left: 20px;
            margin: 32px 0;
        }

        .footer {
            padding: 40px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }

        .social-links { margin-top: 20px; }
        .social-links a { margin: 0 10px; color: #cbd5e1; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header site-{{ $order->site_id }}">
            <div class="logo">{{ $order->site?->name ?? 'Our Store' }}</div>
            <div class="status-badge">Order Confirmed</div>
        </div>

        <div class="content">
            <h1>Hi {{ $order->customer_name }},</h1>
            <p>Your order has been placed successfully! We're already getting things ready for you. Your tracking ID is <strong>#{{ strtoupper($order->tracking_id) }}</strong>.</p>

            <div class="order-card">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 16px;">Order Items</div>
                
                @foreach($order->items as $item)
                <div class="item">
                    <span class="item-name">
                        {{ $item->quantity }}x {{ $item->name }}
                        @if($item->variation_info)
                            <small style="color: #64748b; display: block; font-weight: 400;">({{ $item->variation_info }})</small>
                        @endif
                    </span>
                    <span class="item-price">৳{{ number_format($item->price * $item->quantity, 0) }}</span>
                </div>
                @endforeach

                <div class="divider"></div>

                <div class="item" style="color: #64748b;">
                    <span>Subtotal</span>
                    <span>৳{{ number_format($order->subtotal, 0) }}</span>
                </div>
                <div class="item" style="color: #64748b;">
                    <span>Delivery Fee</span>
                    <span>৳{{ number_format($order->delivery_charge, 0) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="item" style="color: #ef4444;">
                    <span>Discount</span>
                    <span>-৳{{ number_format($order->discount_amount, 0) }}</span>
                </div>
                @endif

                <div class="total-row">
                    <span class="total-label">Total Amount</span>
                    <span class="total-value">৳{{ number_format($order->total_amount, 0) }}</span>
                </div>
            </div>

            <div class="address-box">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 8px;">Shipping Address</div>
                <p style="margin: 0; font-weight: 600; font-size: 14px;">{{ $order->customer_address }}</p>
                <p style="margin: 4px 0 0; color: #64748b; font-size: 13px;">{{ $order->customer_phone }}</p>
            </div>

            <a href="{{ url(($order->site_id == 1 ? 'https://acharu.com' : 'https://tajashutki.com') . '/track?tracking_id=' . $order->tracking_id) }}" class="button site-{{ $order->site_id }}">Track Your Order</a>
        </div>

        <div class="footer">
            <p>You received this email because you made a purchase at {{ $order->site?->name }}.</p>
            <p>&copy; {{ date('Y') }} {{ $order->site?->name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
