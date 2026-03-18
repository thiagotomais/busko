<?php

namespace App\Services\Auth;

use App\Enums\UserType;
use App\Events\GuardianRegistered;
use App\Models\Guardian;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuardianAuthService implements AuthServiceInterface
{
    public function __construct(
        private TokenService $tokenService
    ) {}

    /**
     * Register a new guardian.
     *
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $data['name'] ?? 'Guardian Tenant',
                'slug' => $this->generateSlug($data['email']),
            ]);

            // Set tenant context
            app()->singleton('current_tenant_id', fn() => $tenant->id);

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'type' => UserType::GUARDIAN,
            ]);

            // Create guardian
            $guardian = Guardian::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'cpf' => $data['cpf'],
                'address_id' => $data['address_id'] ?? null,
            ]);

            // Dispatch event
            event(new GuardianRegistered($guardian));

            return $user;
        });
    }

    /**
     * Login a guardian.
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

        // Get guardian without global scope to avoid tenant filtering
        $guardian = Guardian::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->first();
        
        if (!$guardian) {
            return null;
        }

        // Set tenant context for subsequent operations
        app()->bind('current_tenant_id', fn() => $guardian->tenant_id);

        $token = $this->tokenService->createToken($user);

        return [
            'user' => $user,
            'token' => $token,
            'tenant_id' => $guardian->tenant_id,
        ];
    }

    /**
     * Get the user type this service handles.
     *
     * @return UserType
     */
    public function getUserType(): UserType
    {
        return UserType::GUARDIAN;
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

