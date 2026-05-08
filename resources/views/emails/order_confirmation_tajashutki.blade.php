<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .header { background-color: #059669; padding: 40px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: 2px; }
        .header p { margin: 10px 0 0; opacity: 0.8; font-size: 14px; text-transform: uppercase; }
        
        .content { padding: 30px; }
        .welcome-text { font-size: 18px; color: #333; margin-bottom: 20px; font-weight: 600; }
        
        /* THE INVOICE CARD IN EMAIL */
        .invoice-card {
            background: #059669;
            border-radius: 24px;
            padding: 30px;
            color: #ffffff;
            margin-bottom: 30px;
            position: relative;
        }
        .card-chip { width: 45px; height: 35px; background: #FFD700; border-radius: 8px; margin-bottom: 20px; }
        .card-number { font-size: 22px; font-weight: 700; letter-spacing: 3px; margin-bottom: 30px; }
        .card-footer { display: flex; justify-content: space-between; align-items: flex-end; }
        .card-label { font-size: 10px; opacity: 0.7; text-transform: uppercase; margin-bottom: 4px; }
        .card-val { font-size: 16px; font-weight: 600; }

        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th { text-align: left; padding: 12px; border-bottom: 2px solid #f0f0f0; color: #888; font-size: 12px; text-transform: uppercase; }
        .details-table td { padding: 15px 12px; border-bottom: 1px solid #f9f9f9; }
        .item-name { font-weight: 600; color: #333; }
        .item-meta { font-size: 12px; color: #999; }
        
        .summary { background-color: #ecfdf5; padding: 20px; border-radius: 12px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; color: #555; }
        .summary-total { border-top: 1px solid #c9ede0; padding-top: 10px; margin-top: 10px; font-weight: 800; font-size: 20px; color: #059669; }
        
        .footer { padding: 30px; text-align: center; background-color: #fafafa; color: #999; font-size: 12px; }
        .btn { display: inline-block; padding: 15px 30px; background-color: #059669; color: #ffffff !important; text-decoration: none; border-radius: 100px; font-weight: 700; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p>Order Confirmed</p>
            <h1>TAJA SHUTKI</h1>
        </div>
        
        <div class="content">
            <div class="welcome-text">Hi {{ $order->customer_name }},</div>
            <p style="color: #666; line-height: 1.6;">Your order is confirmed! Your tracking ID is <strong>#{{ $order->tracking_id }}</strong>. We are getting your fresh dry fish ready!</p>
            
            <!-- INVOICE SECTION AS A CARD -->
            <div class="invoice-card">
                <div class="card-chip"></div>
                <div class="card-number">**** **** ৳{{ number_format($order->total_amount, 0) }}</div>
                <table width="100%">
                    <tr>
                        <td align="left">
                            <div class="card-label">Customer</div>
                            <div class="card-val">{{ $order->customer_name }}</div>
                        </td>
                        <td align="right" style="text-align: right;">
                            <div class="card-label">Method</div>
                            <div class="card-val">{{ strtoupper($order->payment_method) }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- BILLING INFO -->
            <div style="margin-bottom: 30px; padding: 20px; background: #ecfdf5; border-radius: 12px; border: 1px solid #c9ede0;">
                <div style="font-size: 10px; font-weight: 900; color: #059669; text-transform: uppercase; margin-bottom: 10px;">Billing Address</div>
                <div style="font-size: 14px; color: #333; line-height: 1.5;">
                    <strong>{{ $order->customer_name }}</strong><br>
                    {{ $order->customer_phone }}<br>
                    {{ $order->customer_address }}
                </div>
            </div>

            <table class="details-table">
                <thead>
                    <tr>
                        <th>Items</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->name }}</div>
                            <div class="item-meta">
                                SKU: {{ $item->sku }} | 
                                @if($item->variation_info)
                                    {{ $item->variation_info }} |
                                @endif
                                Rate: ৳{{ number_format($item->price, 0) }}
                            </div>
                        </td>
                        <td style="text-align: center; font-weight: 600;">{{ $item->quantity }}</td>
                        <td style="text-align: right; font-weight: 700; color: #333;">৳{{ number_format($item->price * $item->quantity, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary">
                <table width="100%">
                    <tr>
                        <td style="color: #888;">Subtotal</td>
                        <td align="right" style="font-weight: 600;">৳{{ number_format($order->subtotal, 0) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #888;">Delivery</td>
                        <td align="right" style="font-weight: 600;">৳{{ number_format($order->delivery_charge, 0) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                    <tr>
                        <td style="color: #ef4444;">Discount</td>
                        <td align="right" style="font-weight: 600; color: #ef4444;">-৳{{ number_format($order->discount_amount, 0) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding-top: 15px; font-weight: 800; font-size: 18px;">Total Payable</td>
                        <td align="right" style="padding-top: 15px; font-weight: 800; font-size: 24px; color: #059669;">৳{{ number_format($order->total_amount, 0) }}</td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin-top: 40px;">
                <p style="color: #888; font-size: 14px;">Track your fresh seafood delivery status here.</p>
                <a href="{{ $order->site?->slug === 'acharu' ? 'https://acharu.com/track' : 'https://tajashutki.com/track' }}?id={{ $order->tracking_id }}" class="btn">Track Order Status</a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $order->site?->name ?? 'Taja Shutki' }}. All rights reserved.</p>
            <p>Fresh From Cox's Bazar • Pure & Chemical Free</p>
        </div>
    </div>
</body>
</html>
