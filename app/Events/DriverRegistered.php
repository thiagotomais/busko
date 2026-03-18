<?php

namespace App\Events;

use App\Models\Driver;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Driver $driver
    ) {}
}
