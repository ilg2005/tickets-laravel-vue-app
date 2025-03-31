<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
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
        // 1. Удаляем все физические файлы, связанные с тикетом
        foreach ($ticket->files as $file) {
            // Используем тот же диск ('local'), на который сохраняли
            Storage::disk('local')->delete($file->path);
        }

        // 2. Удаляем саму директорию тикета (опционально, но рекомендуется для чистоты)
        // Это удалит папку storage/app/ticket_attachments/{ticket_id}
        Storage::disk('local')->deleteDirectory('ticket_attachments/' . $ticket->id);

        // Записи в таблице ticket_files будут удалены автоматически
        // благодаря ->cascadeOnDelete() в миграции.
    }

    /**
     * Handle the Ticket "deleted" event.
     * Срабатывает после удаления тикета.
     */
    public function deleted(Ticket $ticket): void
    {
        // Логику удаления файлов перенесли в 'deleting'
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
