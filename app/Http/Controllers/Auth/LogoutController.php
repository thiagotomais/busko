<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        private TokenService $tokenService
    ) {}

    /**
     * Handle logout request (revoke all tokens).
     * POST /api/v1/auth/logout
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Not authenticated',
            ], 401);
        }

        $this->tokenService->revokeTokens($user);

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
