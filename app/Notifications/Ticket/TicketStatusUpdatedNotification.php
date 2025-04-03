<?php

namespace App\Notifications\Ticket;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket\Ticket;
use App\Models\User;

class TicketStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Ticket $ticket;
    public string $oldStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $oldStatus)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/tickets/' . $this->ticket->id);

        return (new MailMessage)
                    ->subject('Статус тикета обновлен: ' . $this->ticket->title)
                    ->greeting('Здравствуйте, ' . $notifiable->name . '!')
                    ->line('Статус тикета №' . $this->ticket->id . ' ("' . $this->ticket->title . '") был изменен.')
                    ->line('Старый статус: ' . $this->oldStatus)
                    ->line('Новый статус: ' . $this->ticket->status)
                    ->action('Просмотреть тикет', $url)
                    ->line('Спасибо за использование нашей системы!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->ticket->status,
        ];
    }
}
