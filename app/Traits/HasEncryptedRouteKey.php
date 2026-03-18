<?php

namespace App\Traits;

use Hashids\Hashids;

trait HasEncryptedRouteKey
{
    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey()
    {
        return $this->encryptRouteKey($this->{$this->getRouteKeyName()});
    }

    /**
     * Retrieve a model by its route key.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $decrypted = $this->decryptRouteKey($value);
        
        if ($decrypted === null) {
            return null;
        }

        return $this->where($field ?? $this->getRouteKeyName(), $decrypted)->first();
    }

    /**
     * Encrypt the route key value.
     */
    private function encryptRouteKey($value): string
    {
        $hashids = new Hashids(config('app.key'), 8);
        return $hashids->encode($value);
    }

    /**
     * Decrypt the route key value.
     */
    private function decryptRouteKey($encrypted): ?int
    {
        try {
            $hashids = new Hashids(config('app.key'), 8);
            $decoded = $hashids->decode($encrypted);
            
            return !empty($decoded) ? $decoded[0] : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
