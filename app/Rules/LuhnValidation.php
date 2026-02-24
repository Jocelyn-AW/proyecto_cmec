<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LuhnValidation implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) !== 16) {
            return;
        }

        if (!$this->isValidLuhn($value)) {
            $fail('El número de tarjeta ingresado no es válido. Por favor, verifique los dígitos.');
        }
    }

    private function isValidLuhn($number): bool
    {
        $sum = 0;
        $shouldDouble = false;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];

            if ($shouldDouble) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $shouldDouble = !$shouldDouble;
        }

        return ($sum % 10 === 0);
    }
}
