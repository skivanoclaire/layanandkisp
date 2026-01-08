<?php

namespace App\Policies;

use App\Models\TteAssistanceRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TteAssistanceRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TteAssistanceRequest $tteAssistanceRequest): bool
    {
        // User can only view their own requests
        return $user->id === $tteAssistanceRequest->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TteAssistanceRequest $tteAssistanceRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TteAssistanceRequest $tteAssistanceRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TteAssistanceRequest $tteAssistanceRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TteAssistanceRequest $tteAssistanceRequest): bool
    {
        return false;
    }
}
