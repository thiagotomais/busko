<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCnh implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnh = preg_replace('/\D/', '', (string)$value);

        // Check if it has exactly 11 digits
        if (strlen($cnh) !== 11) {
            $fail("The {$attribute} must have exactly 11 digits.");
            return;
        }

        // Check if all digits are the same (invalid CNH)
        if (preg_match('/^(\d)\1{10}$/', $cnh)) {
            $fail("The {$attribute} is invalid.");
            return;
        }

        // Basic validation: first 9 digits should not all be zeros and
        // the number should not be too small (basic check)
        if ((int)substr($cnh, 0, 9) === 0) {
            $fail("The {$attribute} is invalid.");
        }
    }
}
