<?php

namespace App\Policies;

use App\Models\Guardian;
use App\Models\User;

class GuardianPolicy extends BasePolicy
{
    /**
     * Determine if the guardian is owned by the authenticated user.
     */
    public function view(User $user, Guardian $guardian): bool
    {
        // Guardian can only view their own data
        if ($user->guardian && $user->guardian->id === $guardian->id) {
            return true;
        }

        // Fall back to tenant check
        return parent::view($user, $guardian);
    }

    /**
     * Determine if the guardian can be updated.
     */
    public function update(User $user, Guardian $guardian): bool
    {
        // Guardian can only update their own data
        if ($user->guardian && $user->guardian->id === $guardian->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the guardian can be deleted.
     */
    public function delete(User $user, Guardian $guardian): bool
    {
        // Guardian cannot delete themselves or others
        return false;
    }
}
