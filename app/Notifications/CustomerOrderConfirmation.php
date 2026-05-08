<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerOrderConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $siteName = $order->site?->name ?? 'Our Store';

        $mail = (new MailMessage)
            ->subject("Order Confirmed — {$order->tracking_id}")
            ->greeting("Hi {$order->customer_name},")
            ->line("Thank you for your order on **{$siteName}**!")
            ->line("Your tracking ID is: **{$order->tracking_id}**")
            ->line('---');

        $mail->line('**Order Summary:**');
        foreach ($order->items as $item) {
            $name = $item->name;
            if ($item->variation_info) {
                $name .= " ({$item->variation_info})";
            }
            $mail->line("- {$name} x {$item->quantity} = ৳" . number_format($item->price * $item->quantity, 2));
        }

        $mail->line('---');
        $mail->line("Subtotal: ৳" . number_format($order->subtotal, 2));
        $mail->line("Delivery: ৳" . number_format($order->delivery_charge, 2));

        if ($order->discount_amount > 0) {
            $mail->line("Discount: -৳" . number_format($order->discount_amount, 2));
        }

        $mail->line("**Total: ৳" . number_format($order->total_amount, 2) . "**");
        $mail->line('---');

        $mail->line("**Delivery Address:** {$order->customer_address}");
        $mail->line("**Payment Method:** " . strtoupper($order->payment_method));

        $mail->action('Track Your Order', url("/track?tracking_id={$order->tracking_id}"))
            ->line("We'll notify you when your order status changes.")
            ->line("Thank you for choosing {$siteName}!");

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'tracking_id' => $this->order->tracking_id,
        ];
    }
}
