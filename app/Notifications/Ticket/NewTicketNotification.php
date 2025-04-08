<?php

namespace App\Notifications\Ticket;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket\Ticket;
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
                    ->subject('New Ticket Created: ' . $this->ticket->title)
                    ->greeting('Hello, ' . $notifiable->name . '!')
                    ->line('A new ticket #' . $this->ticket->id . ' with subject "' . $this->ticket->title . '" has been created.')
                    ->line('Status: ' . $this->ticket->status)
                    ->line('Priority: ' . $this->ticket->priority);
                    
        // Добавляем информацию о файлах, если они есть
        if ($this->ticket->files->count() > 0) {
            $message->line('Files attached to the ticket:');
            foreach ($this->ticket->files as $file) {
                $message->line('- ' . $file->original_filename . ' (' . round($file->size / 1024, 2) . ' KB)');
            }
        }

        return $message->action('View Ticket', $url)
                       ->line('Thank you for using our application!');
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
