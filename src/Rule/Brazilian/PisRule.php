<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Brazilian;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates Brazilian PIS/PASEP/NIT number.
 *
 * Implements the check-digit algorithm (Módulo 11 with weights 3,2,9,8,7,6,5,4,3,2).
 *
 * References:
 * - Decreto nº 97.936/1989 (PIS/PASEP)
 *
 * @package KaririCode\Validator\Rule\Brazilian
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class PisRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', $value);

        if ($digits === null || strlen($digits) !== 11) {
            return false;
        }

        // All same digits = invalid
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        $weights = [3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;

        for ($i = 0; $i < 10; ++$i) {
            $sum += (int) $digits[$i] * $weights[$i];
        }

        $remainder = $sum % 11;
        $check = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $digits[10] === $check;
    }

    public function getErrorCode(): string
    {
        return 'brazilian.pis';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} is not a valid PIS/PASEP.';
    }
}
