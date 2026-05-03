<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AdminNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
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

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'title' => $this->data['title'] ?? 'New Notification',
            'message' => $this->data['message'] ?? '',
            'type' => $this->data['type'] ?? 'info',
            'link' => $this->data['link'] ?? null,
            'store' => $this->data['store'] ?? null,
            'created_at' => now()->toISOString(),
        ]);
    }
}
