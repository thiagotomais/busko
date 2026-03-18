<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\Auth\DriverAuthService;
use App\Services\Auth\GuardianAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private DriverAuthService $driverAuthService,
        private GuardianAuthService $guardianAuthService
    ) {}

    /**
     * Handle login request.
     * POST /api/v1/auth/login
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $userType = UserType::from($validated['type']);
        $authService = $userType === UserType::DRIVER 
            ? $this->driverAuthService 
            : $this->guardianAuthService;

        $result = $authService->login(
            $validated['email'],
            $validated['password'],
            $userType
        );

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
}

