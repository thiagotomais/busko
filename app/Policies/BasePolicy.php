<?php

namespace App\Policies;

use App\Models\User;

class BasePolicy
{
    /**
     * Verify tenant ownership.
     * Returns true if user belongs to the same tenant as the checked model.
     */
    protected function belongsToSameTenant(User $user, $model): bool
    {
        $userTenantId = null;
        $modelTenantId = null;

        // Get user's tenant ID
        if ($user->driver) {
            $userTenantId = $user->driver->tenant_id;
        } elseif ($user->guardian) {
            $userTenantId = $user->guardian->tenant_id;
        }

        // Get model's tenant ID
        if (method_exists($model, 'getTenantIdAttribute')) {
            $modelTenantId = $model->tenant_id;
        } elseif (isset($model->tenant_id)) {
            $modelTenantId = $model->tenant_id;
        }

        return $userTenantId && $modelTenantId && $userTenantId === $modelTenantId;
    }

    /**
     * Determine if the given model can be viewed by the user.
     * By default, all authenticated users can view resources within their tenant.
     */
    public function view(User $user, $model): bool
    {
        return $this->belongsToSameTenant($user, $model);
    }

    /**
     * Determine if the given model can be updated by the user.
     * By default, only the owner/creator can update.
     */
    public function update(User $user, $model): bool
    {
        return $this->belongsToSameTenant($user, $model);
    }

    /**
     * Determine if the given model can be deleted by the user.
     * By default, only the owner/creator can delete.
     */
    public function delete(User $user, $model): bool
    {
        return $this->belongsToSameTenant($user, $model);
    }

    /**
     * Determine if the user can create a model.
     */
    public function create(User $user): bool
    {
        return true;
    }
}
