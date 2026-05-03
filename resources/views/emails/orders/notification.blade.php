@component('mail::message')
# Order Confirmation

Dear {{ $order->customer_name }},

Thank you for your order! We've received your request and are currently processing it. Below are your order details.

<div style="background: #f8fafc; padding: 25px; border-radius: 15px; border: 1px solid #e2e8f0; margin: 20px 0;">
    <p style="margin: 0; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; font-weight: 800;">Tracking ID</p>
    <p style="margin: 5px 0 0 0; font-size: 24px; color: #800000; font-weight: 900;">{{ $order->tracking_id }}</p>
</div>

### Order Summary

@component('mail::table')
| Product | Qty | Price | Total |
| :--- | :---: | :---: | :---: |
@foreach($order->items as $item)
| {{ $item->name }} | {{ $item->quantity }} | ৳{{ number_format($item->price) }} | ৳{{ number_format($item->price * $item->quantity) }} |
@endforeach
@endcomponent

<div style="margin-top: 20px; border-top: 2px solid #f1f5f9; padding-top: 20px;">
    <table width="100%" style="font-family: sans-serif;">
        <tr>
            <td style="color: #64748b; font-weight: 600;">Subtotal:</td>
            <td style="text-align: right; font-weight: 800;">৳{{ number_format($order->subtotal) }}</td>
        </tr>
        <tr>
            <td style="color: #64748b; font-weight: 600;">Delivery Charge:</td>
            <td style="text-align: right; font-weight: 800;">৳{{ number_format($order->delivery_charge) }}</td>
        </tr>
        <tr style="font-size: 20px; color: #800000;">
            <td style="padding-top: 10px; font-weight: 900;">Total Amount:</td>
            <td style="padding-top: 10px; text-align: right; font-weight: 900;">৳{{ number_format($order->total_amount) }}</td>
        </tr>
    </table>
</div>

### Shipping Details
**Address:** {{ $order->customer_address }}  
**Phone:** {{ $order->customer_phone }}  
**Method:** {{ strtoupper($order->payment_method) }}

@component('mail::button', ['url' => config('app.url') . '/track/' . $order->tracking_id, 'color' => 'maroon'])
Track Your Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
