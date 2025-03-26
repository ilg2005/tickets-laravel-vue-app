<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Followup;
use App\Models\User;

class NewFollowupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Followup $followup;

    /**
     * Create a new notification instance.
     */
    public function __construct(Followup $followup)
    {
        $this->followup = $followup;
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
        $ticket = $this->followup->ticket;
        $creator = $this->followup->user;
        $url = url('/tickets/' . $ticket->id);

        $line = $creator->name . ' добавил ' . ($this->followup->type === 'solution' ? 'решение' : 'комментарий') . ' к тикету №' . $ticket->id . ':';

        return (new MailMessage)
                    ->subject('Новый ответ в тикете: ' . $ticket->title)
                    ->greeting('Здравствуйте, ' . $notifiable->name . '!')
                    ->line($line)
                    ->line('> ' . nl2br(e($this->followup->content)))
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
            'followup_id' => $this->followup->id,
            'ticket_id' => $this->followup->ticket_id,
        ];
    }
}
