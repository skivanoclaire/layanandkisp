<?php

namespace App\Policies;

use App\Models\TtePassphraseResetRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TtePassphraseResetRequestPolicy
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
    public function view(User $user, TtePassphraseResetRequest $ttePassphraseResetRequest): bool
    {
        // User can only view their own requests
        return $user->id === $ttePassphraseResetRequest->user_id;
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
    public function update(User $user, TtePassphraseResetRequest $ttePassphraseResetRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TtePassphraseResetRequest $ttePassphraseResetRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TtePassphraseResetRequest $ttePassphraseResetRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TtePassphraseResetRequest $ttePassphraseResetRequest): bool
    {
        return false;
    }
}
