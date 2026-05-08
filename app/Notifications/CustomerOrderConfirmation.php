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
        $siteSlug = $order->site?->slug ?? 'default';
        $siteName = $order->site?->name ?? 'Our Store';
        $siteEmail = config('mail.from.address');

        $view = ($siteSlug === 'acharu') 
            ? 'emails.order_confirmation_acharu' 
            : (($siteSlug === 'tajashutki') ? 'emails.order_confirmation_tajashutki' : 'emails.order_confirmation');

        // Generate PDF Invoice
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', ['order' => $order])
            ->setPaper('a4', 'portrait');

        return (new MailMessage)
            ->from($siteEmail, $siteName)
            ->subject("Order Confirmed — {$order->tracking_id}")
            ->view($view, ['order' => $order])
            ->attachData($pdf->output(), "Invoice_{$order->tracking_id}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'tracking_id' => $this->order->tracking_id,
        ];
    }
}
