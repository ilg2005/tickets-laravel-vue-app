<?php

namespace App\Notifications\Ticket;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket\Followup;
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
        $this->followup = $followup->load('files');
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

        $line = $creator->name . ' added ' . ($this->followup->type === 'solution' ? 'a solution' : 'a comment') . ' to ticket #' . $ticket->id . ':';

        $message = (new MailMessage)
                    ->subject('New Reply in Ticket: ' . $ticket->title)
                    ->greeting('Hello, ' . $notifiable->name . '!')
                    ->line($line)
                    ->line('> ' . nl2br(e($this->followup->content)));
                    
        // Добавляем информацию о файлах, если они есть
        if ($this->followup->files->count() > 0) {
            $message->line('Files attached to the reply:');
            foreach ($this->followup->files as $file) {
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
            'followup_id' => $this->followup->id,
            'ticket_id' => $this->followup->ticket_id,
        ];
    }
}
