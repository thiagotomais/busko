<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Get authenticated user data.
     * GET /api/v1/auth/me
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Not authenticated',
            ], 401);
        }

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type->value,
                'created_at' => $user->created_at,
            ],
            'tenant_id' => $user->tenant_id,
        ];

        // Add driver or guardian specific data
        if ($user->driver) {
            $data['driver'] = [
                'id' => $user->driver->id,
                'cpf' => $user->driver->cpf,
                'cnh' => $user->driver->cnh,
                'address_id' => $user->driver->address_id,
            ];
            $data['tenant_id'] = $user->driver->tenant_id;
        } elseif ($user->guardian) {
            $data['guardian'] = [
                'id' => $user->guardian->id,
                'cpf' => $user->guardian->cpf,
                'address_id' => $user->guardian->address_id,
            ];
            $data['tenant_id'] = $user->guardian->tenant_id;
        }

        return response()->json([
            'message' => 'User data retrieved successfully',
            'data' => $data,
        ], 200);
    }
}
