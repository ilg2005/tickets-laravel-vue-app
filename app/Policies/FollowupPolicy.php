<?php

namespace App\Policies;

use App\Models\Followup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class FollowupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): void
    {
        // return $user->can('view followups');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Followup $followup): bool
    {
        return $user->can('view followups');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create followups');

    }

    public function createComment(User $user)
    {
        return $user->can('create comment followups');
    }

    public function createSolution(User $user)
    {
        return $user->can('create solution followups');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Followup $followup): bool
    {
        return $user->can('update followups') && $user->id === $followup->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Followup $followup): bool
    {
        return ($user->hasRole('admin') && $user->can('delete followups'))  || ($user->can('delete followups') && $user->id === $followup->user_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Followup $followup): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Followup $followup): void
    {
        //
    }
}
