<x-mail::message>
# Order Confirmation

Hello {{ $order->customer_name }},

Thank you for your order with **{{ $order->site->name }}**! 
Your order is currently being processed.

**Order ID:** {{ $order->tracking_id }}  
**Total Amount:** ৳{{ number_format($order->total_amount, 2) }}

<x-mail::button :url="'http://localhost:5173/track/' . $order->tracking_id">
Track Your Order
</x-mail::button>

Thanks,<br>
{{ $order->site->name }} Team
</x-mail::message>
