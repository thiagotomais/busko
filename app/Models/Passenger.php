<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passenger extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'guardian_id',
        'service_type',
        'name',
        'birth_date',
        'school_grade',
        'period',
        'rg',
        'residential_zip',
        'residential_street',
        'residential_number',
        'residential_complement',
        'residential_neighborhood',
        'residential_city',
        'residential_state',
        'pickup_zip',
        'pickup_street',
        'pickup_number',
        'pickup_complement',
        'pickup_neighborhood',
        'pickup_city',
        'pickup_state',
        'dropoff_zip',
        'dropoff_street',
        'dropoff_number',
        'dropoff_complement',
        'dropoff_neighborhood',
        'dropoff_city',
        'dropoff_state',
        'school_name',
        'school_zip',
        'school_street',
        'school_number',
        'school_complement',
        'school_neighborhood',
        'school_city',
        'school_state',
        'entry_time',
        'exit_time',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * Get the tenant associated with this passenger.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the guardian responsible for this passenger.
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }
}
