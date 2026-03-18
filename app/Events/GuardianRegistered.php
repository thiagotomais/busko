<?php

namespace App\Events;

use App\Models\Guardian;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuardianRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Guardian $guardian
    ) {}
}
