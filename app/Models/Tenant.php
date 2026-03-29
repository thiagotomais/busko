<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'uid',
        'name',
        'slug',
        'is_active',
        'bank_code',
        'bank_branch',
        'bank_account',
        'bank_account_type',
        'pix_key',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tenant): void {
            if (empty($tenant->uid)) {
                $tenant->uid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get all drivers for this tenant.
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    /**
     * Get all guardians for this tenant.
     */
    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    /**
     * Get all users for this tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all addresses for this tenant.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get all passengers for this tenant.
     */
    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    /**
     * Get all driver-guardian relationships for this tenant.
     */
    public function driverGuardians(): HasMany
    {
        return $this->hasMany(DriverGuardian::class);
    }

    /**
     * Get all personal access tokens for this tenant.
     */
    public function personalAccessTokens(): HasMany
    {
        return $this->hasMany(PersonalAccessToken::class);
    }
}

