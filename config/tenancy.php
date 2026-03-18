<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenancy Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to multi-tenancy behavior.
    |
    */

    'tenant_id_header' => 'X-Tenant-Id',

    'tenant_resolver' => \App\Services\Tenant\TenantResolver::class,

];
