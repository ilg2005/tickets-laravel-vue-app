<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;
use App\Models\User;

class NewTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Ticket $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket->load('files');
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

        $message = (new MailMessage)
                    ->subject('Новый тикет создан: ' . $this->ticket->title)
                    ->greeting('Здравствуйте, ' . $notifiable->name . '!')
                    ->line('Был создан новый тикет №' . $this->ticket->id . ' с темой "' . $this->ticket->title . '".')
                    ->line('Статус: ' . $this->ticket->status)
                    ->line('Приоритет: ' . $this->ticket->priority);
                    
        // Добавляем информацию о файлах, если они есть
        if ($this->ticket->files->count() > 0) {
            $message->line('К тикету прикреплены файлы:');
            foreach ($this->ticket->files as $file) {
                $message->line('- ' . $file->original_filename . ' (' . round($file->size / 1024, 2) . ' KB)');
            }
        }

        return $message->action('Просмотреть тикет', $url)
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
            'title' => $this->ticket->title,
        ];
    }
}
