<?php

namespace App\Observers\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\User;
use App\Notifications\Ticket\NewTicketNotification;
use App\Notifications\Ticket\TicketStatusUpdatedNotification;
use App\Services\Ticket\FileService;
use Illuminate\Support\Facades\Notification;

class TicketObserver
{
    /**
     * Сервис для работы с файлами
     *
     * @var FileService
     */
    protected FileService $fileService;

    /**
     * Конструктор наблюдателя с внедрением зависимости FileService
     *
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        // Загружаем файлы перед отправкой уведомления
        $ticket->load('files');
        
        $ticket->user->notify(new NewTicketNotification($ticket));

        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->where('id', '!=', $ticket->user_id)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewTicketNotification($ticket));
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if ($ticket->isDirty('status')) {
            $oldStatus = $ticket->getOriginal('status');
            $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus));
        }
    }

    /**
     * Handle the Ticket "deleting" event.
     * Срабатывает перед удалением тикета.
     */
    public function deleting(Ticket $ticket): void
    {
        // Используем FileService для удаления всех файлов тикета
        $this->fileService->deleteFiles($ticket);
    }

    /**
     * Handle the Ticket "deleted" event.
     * Срабатывает после удаления тикета.
     */
    public function deleted(Ticket $ticket): void
    {
        // Логика удаления файлов перенесена в 'deleting'
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
