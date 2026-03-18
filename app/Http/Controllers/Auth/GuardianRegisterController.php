<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardianRegisterRequest;
use App\Services\Auth\GuardianAuthService;
use App\Services\Auth\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class GuardianRegisterController extends Controller
{
    public function __construct(
        private GuardianAuthService $guardianAuthService,
        private TokenService $tokenService
    ) {}

    /**
     * Handle guardian registration request.
     * POST /api/v1/auth/guardians/register
     */
    public function store(GuardianRegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $user = $this->guardianAuthService->register($validated);
            $token = $this->tokenService->createToken($user, 'guardian-api-token');
            $tenantId = $user->guardian->tenant_id;

            return response()->json([
                'message' => 'Guardian registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'type' => $user->type->value,
                    ],
                    'guardian' => [
                        'id' => $user->guardian->id,
                        'cpf' => $user->guardian->cpf,
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
