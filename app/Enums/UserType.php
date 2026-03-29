<?php

namespace App\Enums;

enum UserType: string
{
    case DRIVER = 'driver';
    case GUARDIAN = 'guardian';
    case ADMIN = 'admin';
    case COMPANY_ADMIN = 'company_admin';
}
