<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $store = $this->data['store'] ?? null;
        $fromName = match ($store) {
            'acharu' => 'Acharu',
            'tajashutki' => 'Taja Shutki',
            default => config('mail.from.name', 'Multi Vendor'),
        };

        return (new MailMessage)
            ->from(config('mail.from.address'), $fromName)
            ->subject($this->data['title'] ?? 'Notification')
            ->greeting('Hello ' . ($notifiable->name ?? 'Admin') . ',')
            ->line($this->data['message'] ?? '')
            ->when($this->data['link'] ?? null, fn($msg) => $msg->action('View Details', url('/admin' . $this->data['link'])))
            ->line('Thank you for using our platform.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'New Notification',
            'message' => $this->data['message'] ?? '',
            'type' => $this->data['type'] ?? 'info',
            'link' => $this->data['link'] ?? null,
            'store' => $this->data['store'] ?? null,
        ];
    }
}
