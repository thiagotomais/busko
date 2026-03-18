<?php

namespace App\Http\Middleware;

use App\Services\Tenant\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = TenantResolver::resolveTenantId($request);

        if ($tenantId) {
            TenantResolver::setContext($tenantId);
            $request->attributes->set('tenant_id', $tenantId);
        } else {
            // For unauthenticated routes, we might not need a tenant
            TenantResolver::setContext(0); // Default to 0 (no tenant context)
        }

        return $next($request);
    }
}
