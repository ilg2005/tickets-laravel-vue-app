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
        // Администраторы могут видеть все тикеты
        return $user->hasRole(Permissions::ROLE_ADMIN);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Администраторы или владельцы тикета могут просматривать
        return $user->hasRole(Permissions::ROLE_ADMIN) || $user->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Только пользователи (не администраторы) могут создавать тикеты
        return !$user->hasRole(Permissions::ROLE_ADMIN) && $user->hasRole(Permissions::ROLE_USER);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Администраторы или владельцы тикета могут обновлять
        return $user->hasRole(Permissions::ROLE_ADMIN) || $user->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Администраторы или владельцы тикета могут удалять
        return $user->hasRole(Permissions::ROLE_ADMIN) || $user->id === $ticket->user_id;
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
