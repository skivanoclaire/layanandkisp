<?php

namespace App\Policies;

use App\Models\TteRegistrationRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TteRegistrationRequestPolicy
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
    public function view(User $user, TteRegistrationRequest $tteRegistrationRequest): bool
    {
        // User can only view their own requests
        return $user->id === $tteRegistrationRequest->user_id;
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
    public function update(User $user, TteRegistrationRequest $tteRegistrationRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TteRegistrationRequest $tteRegistrationRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TteRegistrationRequest $tteRegistrationRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TteRegistrationRequest $tteRegistrationRequest): bool
    {
        return false;
    }
}
