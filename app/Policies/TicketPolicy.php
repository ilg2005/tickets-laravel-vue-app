<?php

namespace App\Policies;

use App\Models\Ticket\Ticket;
use App\Models\User;
use App\Constants\Permissions;

class TicketPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->can(Permissions::VIEW_TICKETS) && $this->isOwnerOrAdmin($user, $ticket);
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
        return $user->can(Permissions::UPDATE_TICKETS) && $this->isOwnerOrAdmin($user, $ticket);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can(Permissions::DELETE_TICKETS) && $this->isOwnerOrAdmin($user, $ticket);
    }

    /**
     * Проверяет, является ли пользователь владельцем тикета или администратором
     */
    private function isOwnerOrAdmin(User $user, Ticket $ticket): bool
    {
        return $user->can(Permissions::VIEW_ANY_TICKETS) || $user->id === $ticket->user_id;
    }
}
