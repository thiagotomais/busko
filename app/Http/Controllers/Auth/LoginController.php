<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\Auth\DriverAuthService;
use App\Services\Auth\GuardianAuthService;
use App\Services\Auth\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private DriverAuthService $driverAuthService,
        private GuardianAuthService $guardianAuthService,
        private TokenService $tokenService
    ) {}

    /**
     * Handle login request.
     * POST /api/v1/auth/login
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $userType = $this->resolveUserTypeByEmail($validated['email']);
        if (!$userType) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials',
            ]);
        }

        if ($userType === UserType::ADMIN) {
            $result = $this->adminLogin($validated['email'], $validated['password']);
        } else {
            $authService = $userType === UserType::DRIVER
                ? $this->driverAuthService
                : $this->guardianAuthService;

            $result = $authService->login(
                $validated['email'],
                $validated['password'],
                $userType
            );
        }

        if (!$result) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials',
            ]);
        }

        // Dispatch login event
        event(new \App\Events\UserLoggedIn($result['user']));

        return response()->json([
            'message' => 'Logged in successfully',
            'data' => [
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'type' => $result['user']->type->value,
                ],
                'token' => $result['token'],
                'tenant_id' => $result['tenant_id'],
            ],
        ], 200);
    }

    /**
     * Authenticate a global admin user.
     */
    private function adminLogin(string $email, string $password): ?array
    {
        $user = User::with('tenant')
            ->where('email', $email)
            ->where('type', UserType::ADMIN)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (!$user->is_active) {
            return null;
        }

        if ($user->tenant && !$user->tenant->is_active) {
            return null;
        }

        if ($user->tenant_id) {
            app()->bind('current_tenant_id', fn() => $user->tenant_id);
        }

        return [
            'user' => $user,
            'token' => $this->tokenService->createToken($user),
            'tenant_id' => $user->tenant_id,
        ];
    }

    /**
     * Resolve account profile type by email.
     */
    private function resolveUserTypeByEmail(string $email): ?UserType
    {
        $user = User::query()->where('email', $email)->first(['id', 'type']);

        if (!$user) {
            return null;
        }

        if ($user->type instanceof UserType) {
            return $user->type;
        }

        return is_string($user->type) ? UserType::tryFrom($user->type) : null;
    }
}

