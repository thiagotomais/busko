<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;

class DriverPolicy extends BasePolicy
{
    /**
     * Determine if the driver is owned by the authenticated user.
     */
    public function view(User $user, Driver $driver): bool
    {
        // Driver can only view their own data
        if ($user->driver && $user->driver->id === $driver->id) {
            return true;
        }

        // Fall back to tenant check
        return parent::view($user, $driver);
    }

    /**
     * Determine if the driver can be updated.
     */
    public function update(User $user, Driver $driver): bool
    {
        // Driver can only update their own data
        if ($user->driver && $user->driver->id === $driver->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the driver can be deleted.
     */
    public function delete(User $user, Driver $driver): bool
    {
        // Driver cannot delete themselves or others
        return false;
    }
}
