<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .header { background-color: {{ $order->site?->slug === 'acharu' ? '#800000' : '#059669' }}; padding: 40px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; text-transform: uppercase; }
        
        .content { padding: 30px; text-align: center; }
        .status-badge { display: inline-block; padding: 10px 30px; background-color: #f0f0f0; border-radius: 100px; font-size: 18px; font-weight: 800; color: #333; text-transform: uppercase; margin: 20px 0; }
        
        .footer { padding: 30px; text-align: center; background-color: #fafafa; color: #999; font-size: 12px; }
        .btn { display: inline-block; padding: 15px 30px; background-color: {{ $order->site?->slug === 'acharu' ? '#800000' : '#059669' }}; color: #ffffff !important; text-decoration: none; border-radius: 100px; font-weight: 700; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $order->site?->name ?? 'Acharu' }}</h1>
            <p style="margin-top: 10px; opacity: 0.8;">Order Status Update</p>
        </div>
        
        <div class="content">
            <h2 style="color: #333;">Hi {{ $order->customer_name }},</h2>
            <p style="color: #666;">The status of your order <strong>#{{ $order->tracking_id }}</strong> has been updated to:</p>
            
            <div class="status-badge">
                {{ strtoupper($order->status) }}
            </div>
            
            <p style="color: #666; line-height: 1.6; max-width: 400px; margin: 0 auto;">
                @if($order->status === 'shipped')
                    Your package is on its way! Get ready to receive your items.
                @elseif($order->status === 'delivered')
                    Your order has been successfully delivered. Enjoy!
                @elseif($order->status === 'processing')
                    We are currently preparing your items for shipment.
                @else
                    Your order status has changed. Check details below.
                @endif
            </p>

            <a href="{{ $order->site?->slug === 'acharu' ? 'https://acharu.com/track' : 'https://tajashutki.com/track' }}?id={{ $order->tracking_id }}" class="btn">Track Order Live</a>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $order->site?->name ?? 'Acharu' }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
