<?php

namespace App\Policies;

use App\Models\Ticket\Ticket;
use App\Models\User;
use App\Constants\Permissions;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::VIEW_ANY_TICKETS);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->can(Permissions::VIEW_TICKETS) && 
               ($user->can(Permissions::VIEW_ANY_TICKETS) || $user->id === $ticket->user_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permissions::CREATE_TICKETS);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->can(Permissions::UPDATE_TICKETS) && 
               ($user->can(Permissions::VIEW_ANY_TICKETS) || $user->id === $ticket->user_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can(Permissions::DELETE_TICKETS) && 
               ($user->can(Permissions::VIEW_ANY_TICKETS) || $user->id === $ticket->user_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return false; // Not implemented
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false; // Not implemented
    }
}
