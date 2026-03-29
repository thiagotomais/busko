<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TransportRoute extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'driver_id',
        'name',
        'direction',
        'period',
        'vehicle_name',
        'vehicle_plate',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'saturday' => 'boolean',
        'sunday' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the driver assigned to the route.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get passengers assigned to the route.
     */
    public function passengers(): BelongsToMany
    {
        return $this->belongsToMany(Passenger::class, 'transport_route_passengers')
            ->withPivot(['tenant_id', 'stop_order'])
            ->withTimestamps()
            ->orderBy('transport_route_passengers.stop_order');
    }

    /**
     * Human-readable weekday labels.
     *
     * @return array<string, string>
     */
    public static function weekdayLabels(): array
    {
        return [
            'monday' => 'Seg',
            'tuesday' => 'Ter',
            'wednesday' => 'Qua',
            'thursday' => 'Qui',
            'friday' => 'Sex',
            'saturday' => 'Sab',
            'sunday' => 'Dom',
        ];
    }
}