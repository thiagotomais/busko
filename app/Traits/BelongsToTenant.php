<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     * Automatically adds tenant_id to all queries.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            try {
                $tenantId = app()->make('current_tenant_id');
                if ($tenantId) {
                    $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
                }
            } catch (\Exception $e) {
                // current_tenant_id is not yet registered, skip filtering
            }
        });

        static::creating(function ($model) {
            try {
                if (!isset($model->tenant_id) || is_null($model->tenant_id)) {
                    // Try to get from container first
                    try {
                        $tenantId = app()->make('current_tenant_id');
                        if ($tenantId) {
                            $model->tenant_id = $tenantId;
                        }
                    } catch (\Exception $e) {
                        // Continue silently
                    }
                }
            } catch (\Exception $e) {
                // Silently fail - let the database enforce the constraint if needed
            }
        });
    }

    /**
     * Define the relationship to Tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Bypass tenant scope (use with caution).
     */
    public function withoutTenant()
    {
        return $this->withoutGlobalScope('tenant');
    }
}
