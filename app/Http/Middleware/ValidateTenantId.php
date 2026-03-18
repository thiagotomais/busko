<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTenantId
{
    /**
     * Handle an incoming request.
     * Validates that the tenant_id in the request matches the authenticated user's tenant.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only validate if user is authenticated
        if ($request->user()) {
            $userTenantId = $this->getUserTenantId($request->user());
            $requestTenantId = (int)$request->input('tenant_id') ?: (int)$request->header('X-Tenant-Id');

            // If a tenant_id is specified in request, validate it matches user's tenant
            if ($requestTenantId && $requestTenantId !== $userTenantId) {
                return response()->json([
                    'message' => 'Unauthorized tenant access',
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Get the tenant ID from the authenticated user.
     *
     * @param mixed $user
     * @return int|null
     */
    private function getUserTenantId($user): ?int
    {
        if ($user->driver) {
            return $user->driver->tenant_id;
        }
        if ($user->guardian) {
            return $user->guardian->tenant_id;
        }
        return null;
    }
}
