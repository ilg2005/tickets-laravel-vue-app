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
            ->greeting('Здравствуйте, ' . $notifiable->name . '!');

        // Если изменился статус
        if (isset($this->changedFields['status'])) {
            $message->subject('Статус тикета обновлен: ' . $this->ticket->title)
                ->line('Статус тикета №' . $this->ticket->id . ' ("' . $this->ticket->title . '") был изменен.')
                ->line('Старый статус: ' . $this->changedFields['status']['old'])
                ->line('Новый статус: ' . $this->changedFields['status']['new']);
        } else {
            $message->subject('Тикет обновлен: ' . $this->ticket->title)
                ->line('Тикет №' . $this->ticket->id . ' ("' . $this->ticket->title . '") был обновлен.');

            // Выводим список измененных полей
            $fieldLabels = [
                'title' => 'Название',
                'description' => 'Описание',
                'priority' => 'Приоритет'
            ];

            foreach ($this->changedFields as $field => $values) {
                if (isset($fieldLabels[$field])) {
                    if ($field === 'description') {
                        $message->line($fieldLabels[$field] . ' было изменено');
                    } else {
                        $message->line($fieldLabels[$field] . ': ' . $values['old'] . ' → ' . $values['new']);
                    }
                }
            }

            // Добавляем текущий статус и приоритет
            $message->line('Текущий статус: ' . $this->ticket->status)
                   ->line('Текущий приоритет: ' . $this->ticket->priority);
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
            'changed_fields' => $this->changedFields
        ];
    }
}
