<?php

namespace App\Enums;

enum UserType: string
{
    case DRIVER = 'driver';
    case GUARDIAN = 'guardian';
    case ADMIN = 'admin';
}
