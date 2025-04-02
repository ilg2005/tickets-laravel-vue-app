<?php

namespace App\Observers;

use App\Models\Followup;
use App\Models\User;
use App\Notifications\NewFollowupNotification;
use App\Services\FileService;
use Illuminate\Support\Facades\Notification;

class FollowupObserver
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
     * Handle the Followup "created" event.
     */
    public function created(Followup $followup): void
    {
        // Загружаем файлы перед отправкой уведомления
        $followup->load('files');
        
        $ticket = $followup->ticket;
        $ticketOwner = $ticket->user;
        $followupCreator = $followup->user;

        if ($ticketOwner->id !== $followupCreator->id) {
            $ticketOwner->notify(new NewFollowupNotification($followup));
        }

        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->where('id', '!=', $followupCreator->id)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewFollowupNotification($followup));
        }
    }

    /**
     * Handle the Followup "updating" event.
     */
    public function updating(Followup $followup): void
    {
        //
    }

    /**
     * Handle the Followup "updated" event.
     */
    public function updated(Followup $followup): void
    {
        //
    }

    /**
     * Handle the Followup "deleting" event.
     * Срабатывает до удаления модели из базы данных.
     */
    public function deleting(Followup $followup): void
    {
        // Предварительно загружаем связанные файлы, если они еще не загружены
        if (!$followup->relationLoaded('files')) {
            $followup->load('files');
        }
        
        // Используем FileService для удаления всех файлов followup
        $this->fileService->deleteFiles($followup);
    }

    /**
     * Handle the Followup "deleted" event.
     * Срабатывает после удаления модели из базы данных.
     */
    public function deleted(Followup $followup): void
    {
        // Логика удаления файлов перенесена в метод deleting
    }

    /**
     * Handle the Followup "restored" event.
     */
    public function restored(Followup $followup): void
    {
        //
    }

    /**
     * Handle the Followup "force deleted" event.
     */
    public function forceDeleted(Followup $followup): void
    {
        //
    }
}
