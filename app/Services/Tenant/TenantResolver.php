<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantResolver
{
    /**
     * Get the current tenant ID from the request.
     *
     * @param Request $request
     * @return int|null
     */
    public static function resolveTenantId(Request $request): ?int
    {
        // Try to get from authenticated user
        if ($request->user()) {
            return static::getTenantIdFromUser($request->user());
        }

        // Try to get from header (X-Tenant-Id)
        if ($request->hasHeader('X-Tenant-Id')) {
            return (int)$request->header('X-Tenant-Id');
        }

        // Try to get from subdomain
        $tenantSlug = static::extractTenantFromHost($request->getHost());
        if ($tenantSlug && $tenantSlug !== 'api') {
            $tenant = Tenant::where('slug', $tenantSlug)->first();
            if ($tenant) {
                return $tenant->id;
            }
        }

        return null;
    }

    /**
     * Get tenant ID from authenticated user.
     *
     * @param mixed $user
     * @return int|null
     */
    private static function getTenantIdFromUser($user): ?int
    {
        // If user has a driver or guardian relationship
        if ($user instanceof \App\Models\User) {
            if ($user->driver) {
                return $user->driver->tenant_id;
            }
            if ($user->guardian) {
                return $user->guardian->tenant_id;
            }
        }

        return null;
    }

    /**
     * Extract tenant slug from host.
     * Example: tenant.example.com => tenant
     *
     * @param string $host
     * @return string|null
     */
    private static function extractTenantFromHost(string $host): ?string
    {
        $parts = explode('.', $host);
        if (count($parts) >= 2) {
            return $parts[0];
        }
        return null;
    }

    /**
     * Set the current tenant context.
     *
     * @param int $tenantId
     * @return void
     */
    public static function setContext(int $tenantId): void
    {
        app()->singleton('current_tenant_id', fn() => $tenantId);
    }
}
