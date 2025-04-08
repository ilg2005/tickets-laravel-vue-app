<?php

namespace App\Notifications\Ticket;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket\Ticket;

class TicketUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Ticket $ticket;
    public array $changedFields;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, array $changedFields = [])
    {
        $this->ticket = $ticket->load('files');
        $this->changedFields = $changedFields;
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
            ->greeting('Hello, ' . $notifiable->name . '!');

        // Если изменился статус
        if (isset($this->changedFields['status'])) {
            $message->subject('Ticket Status Updated: ' . $this->ticket->title)
                ->line('The status of ticket #' . $this->ticket->id . ' ("' . $this->ticket->title . '") has been changed.')
                ->line('Old status: ' . $this->changedFields['status']['old'])
                ->line('New status: ' . $this->changedFields['status']['new']);
        } else {
            $message->subject('Ticket Updated: ' . $this->ticket->title)
                ->line('Ticket #' . $this->ticket->id . ' ("' . $this->ticket->title . '") has been updated.');

            // Выводим список измененных полей
            $fieldLabels = [
                'title' => 'Title',
                'description' => 'Description',
                'priority' => 'Priority'
            ];

            foreach ($this->changedFields as $field => $values) {
                if (isset($fieldLabels[$field])) {
                    if ($field === 'description') {
                        $message->line($fieldLabels[$field] . ' has been changed');
                    } else {
                        $message->line($fieldLabels[$field] . ': ' . $values['old'] . ' → ' . $values['new']);
                    }
                }
            }

            // Добавляем текущий статус и приоритет
            $message->line('Current status: ' . $this->ticket->status)
                   ->line('Current priority: ' . $this->ticket->priority);
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
            'changed_fields' => $this->changedFields
        ];
    }
}
