<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;

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
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
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
