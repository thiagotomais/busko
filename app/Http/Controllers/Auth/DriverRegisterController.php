<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverRegisterRequest;
use App\Services\Auth\DriverAuthService;
use App\Services\Auth\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DriverRegisterController extends Controller
{
    public function __construct(
        private DriverAuthService $driverAuthService,
        private TokenService $tokenService
    ) {}

    /**
     * Handle driver registration request.
     * POST /api/v1/auth/drivers/register
     */
    public function store(DriverRegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $user = $this->driverAuthService->register($validated);
            $token = $this->tokenService->createToken($user, 'driver-api-token');
            $tenantId = $user->driver->tenant_id;

            return response()->json([
                'message' => 'Driver registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'type' => $user->type->value,
                    ],
                    'driver' => [
                        'id' => $user->driver->id,
                        'cpf' => $user->driver->cpf,
                        'cnh' => $user->driver->cnh,
                    ],
                    'token' => $token,
                    'tenant_id' => $tenantId,
                ],
            ], 201);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
