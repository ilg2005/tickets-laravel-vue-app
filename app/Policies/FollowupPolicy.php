<?php

namespace App\Policies;

use App\Models\Ticket\Followup;
use App\Models\User;
use App\Constants\Permissions;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class FollowupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::VIEW_FOLLOWUPS);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Followup $followup): bool
    {
        return $user->can(Permissions::VIEW_FOLLOWUPS);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permissions::CREATE_FOLLOWUPS);
    }

    /**
     * Определяет, может ли пользователь создавать комментарии
     */
    public function createComment(User $user): bool
    {
        return $user->can(Permissions::CREATE_COMMENT_FOLLOWUPS);
    }

    /**
     * Определяет, может ли пользователь создавать решения
     */
    public function createSolution(User $user): bool
    {
        return $user->can(Permissions::CREATE_SOLUTION_FOLLOWUPS);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Followup $followup): bool
    {
        return $user->can(Permissions::UPDATE_FOLLOWUPS) && $user->id === $followup->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Followup $followup): bool
    {
        // Админы могут удалять любые followups, пользователи - только свои
        return $user->can(Permissions::DELETE_FOLLOWUPS) && 
               ($user->can(Permissions::VIEW_ANY_TICKETS) || $user->id === $followup->user_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Followup $followup): bool
    {
        return false; // Not implemented
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Followup $followup): bool
    {
        return false; // Not implemented
    }
}
