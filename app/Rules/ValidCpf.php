<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCpf implements ValidationRule
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
        $cpf = preg_replace('/\D/', '', (string)$value);

        // Check if it has exactly 11 digits
        if (strlen($cpf) !== 11) {
            $fail("The {$attribute} must have exactly 11 digits.");
            return;
        }

        // Check if all digits are the same (invalid CPF)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            $fail("The {$attribute} is invalid.");
            return;
        }

        // Validate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $cpf[$i] * (10 - $i);
        }
        $firstDigit = 11 - ($sum % 11);
        if ($firstDigit > 9) {
            $firstDigit = 0;
        }

        if ($firstDigit !== (int)$cpf[9]) {
            $fail("The {$attribute} is invalid.");
            return;
        }

        // Validate second check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $cpf[$i] * (11 - $i);
        }
        $secondDigit = 11 - ($sum % 11);
        if ($secondDigit > 9) {
            $secondDigit = 0;
        }

        if ($secondDigit !== (int)$cpf[10]) {
            $fail("The {$attribute} is invalid.");
        }
    }
}
