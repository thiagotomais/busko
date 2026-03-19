<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Guardian extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'primary_driver_id',
        'cpf',
        'address_id',
        'slug',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Retrieve a model by its route key.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return static::where('slug', $value)->first()
            ?? abort(404, 'Guardian not found');
    }

    /**
     * Get the user associated with this guardian.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant associated with this guardian.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the address associated with this guardian.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get the primary driver for this guardian.
     */
    public function primaryDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'primary_driver_id');
    }

    /**
     * Get all drivers associated with this guardian.
     */
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'driver_guardians')
            ->withTimestamps();
    }

    /**
     * Get all passengers linked to this guardian.
     */
    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }
}
