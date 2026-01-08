<?php

namespace App\Policies;

use App\Models\TteCertificateUpdateRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TteCertificateUpdateRequestPolicy
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
    public function view(User $user, TteCertificateUpdateRequest $tteCertificateUpdateRequest): bool
    {
        // User can only view their own requests
        return $user->id === $tteCertificateUpdateRequest->user_id;
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
    public function update(User $user, TteCertificateUpdateRequest $tteCertificateUpdateRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TteCertificateUpdateRequest $tteCertificateUpdateRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TteCertificateUpdateRequest $tteCertificateUpdateRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TteCertificateUpdateRequest $tteCertificateUpdateRequest): bool
    {
        return false;
    }
}
