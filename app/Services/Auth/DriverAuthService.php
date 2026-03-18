<?php

namespace App\Services\Auth;

use App\Enums\UserType;
use App\Events\DriverRegistered;
use App\Models\Driver;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverAuthService implements AuthServiceInterface
{
    public function __construct(
        private TokenService $tokenService
    ) {}

    /**
     * Register a new driver.
     *
     * @param array $data
     * @return User
     * @throws \\Exception
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $data['name'] ?? 'Driver Tenant',
                'slug' => $this->generateSlug($data['email']),
            ]);

            // Set tenant context
            app()->singleton('current_tenant_id', fn() => $tenant->id);

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'type' => UserType::DRIVER,
            ]);

            // Create driver
            $driver = Driver::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'cpf' => $data['cpf'],
                'cnh' => $data['cnh'],
                'address_id' => $data['address_id'] ?? null,
            ]);

            // Dispatch event
            event(new DriverRegistered($driver));

            return $user;
        });
    }

    /**
     * Login a driver.
     *
     * @param string $email
     * @param string $password
     * @param UserType $type
     * @return array|null
     */
    public function login(string $email, string $password, UserType $type): ?array
    {
        $user = User::where('email', $email)
            ->where('type', $type)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        // Get driver without global scope to avoid tenant filtering
        $driver = Driver::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->first();
        
        if (!$driver) {
            return null;
        }

        // Set tenant context for subsequent operations
        app()->bind('current_tenant_id', fn() => $driver->tenant_id);

        $token = $this->tokenService->createToken($user);

        return [
            'user' => $user,
            'token' => $token,
            'tenant_id' => $driver->tenant_id,
        ];
    }

    /**
     * Get the user type this service handles.
     *
     * @return UserType
     */
    public function getUserType(): UserType
    {
        return UserType::DRIVER;
    }

    /**
     * Generate a unique slug from email.
     *
     * @param string $email
     * @return string
     */
    private function generateSlug(string $email): string
    {
        $slug = strtolower(explode('@', $email)[0]);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        
        $original = $slug;
        $count = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}

