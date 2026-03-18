<?php

namespace App\Services\Auth;

use App\Enums\UserType;
use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new user of the given type.
     * 
     * @param array $data
     * @return User
     */
    public function register(array $data): User;

    /**
     * Login a user with email and password.
     *
     * @param string $email
     * @param string $password
     * @param UserType $type
     * @return array|null
     */
    public function login(string $email, string $password, UserType $type): ?array;

    /**
     * Get the user type this service handles.
     *
     * @return UserType
     */
    public function getUserType(): UserType;
}
