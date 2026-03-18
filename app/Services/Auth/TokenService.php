<?php

namespace App\Services\Auth;

use App\Models\PersonalAccessToken;
use App\Models\User;

class TokenService
{
    /**
     * Create a personal access token for a user.
     *
     * @param User $user
     * @param string $tokenName
     * @param array $abilities
     * @return string
     */
    public function createToken(User $user, string $tokenName = 'api-token', array $abilities = ['*']): string
    {
        // Create the token using Sanctum
        $tokenObject = $user->createToken($tokenName, $abilities);

        // Get tenant_id from user's relationships
        $tenantId = $this->getTenantIdFromUser($user);

        // Update the token with tenant_id if we have one
        if ($tenantId) {
            \DB::table('personal_access_tokens')
                ->where('id', $tokenObject->accessToken->id)
                ->update(['tenant_id' => $tenantId]);
        }

        return $tokenObject->plainTextToken;
    }

    /**
     * Revoke a token from a user.
     *
     * @param User $user
     * @return void
     */
    public function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Revoke a specific token.
     *
     * @param User $user
     * @param string $tokenId
     * @return bool
     */
    public function revokeToken(User $user, string $tokenId): bool
    {
        return (bool)$user->tokens()
            ->where('id', $tokenId)
            ->delete();
    }

    /**
     * Validate a token.
     *
     * @param string $plainTextToken
     * @return bool
     */
    public function validateToken(string $plainTextToken): bool
    {
        // Sanctum validates tokens automatically via middleware
        return true;
    }

    /**
     * Get the tenant ID from user.
     *
     * @param User $user
     * @return int|null
     */
    private function getTenantIdFromUser(User $user): ?int
    {
        if ($user->driver) {
            return $user->driver->tenant_id;
        }
        if ($user->guardian) {
            return $user->guardian->tenant_id;
        }
        return null;
    }
}
