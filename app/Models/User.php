<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'type',
        'is_active',
        'is_company_manager',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
            'is_active' => 'boolean',
            'is_company_manager' => 'boolean',
        ];
    }

    /**
     * Get the driver associated with this user.
     */
    public function driver()
    {
        return $this->hasOne(Driver::class, 'user_id');
    }

    /**
     * Get the guardian associated with this user.
     */
    public function guardian()
    {
        return $this->hasOne(Guardian::class, 'user_id');
    }

    /**
     * Get the transport company associated with this user.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

