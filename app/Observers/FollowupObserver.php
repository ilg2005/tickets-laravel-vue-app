<?php

namespace App\Observers;

use App\Models\Followup;
use App\Models\User;
use App\Notifications\NewFollowupNotification;
use Illuminate\Support\Facades\Notification;

class FollowupObserver
{
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
     * Handle the Followup "updated" event.
     */
    public function updated(Followup $followup): void
    {
        //
    }

    /**
     * Handle the Followup "deleted" event.
     */
    public function deleted(Followup $followup): void
    {
        //
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
