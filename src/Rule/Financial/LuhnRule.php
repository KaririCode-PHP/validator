<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Financial;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates credit card numbers using the Luhn algorithm (ISO/IEC 7812-1).
 *
 * Accepts numeric strings with optional spaces/hyphens.
 *
 * References:
 * - ISO/IEC 7812-1:2017 (Identification cards — Numbering system)
 * - Luhn, H.P. "Computer for Verifying Numbers", US Patent 2,950,048 (1960)
 *
 * @package KaririCode\Validator\Rule\Financial
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class LuhnRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value) && !is_int($value)) {
            return false;
        }

        $digits = preg_replace('/[\s-]/', '', (string) $value);

        if ($digits === null || $digits === '' || !ctype_digit($digits) || strlen($digits) < 8) {
            return false;
        }

        $sum = 0;
        $length = strlen($digits);
        $parity = $length % 2;

        for ($i = 0; $i < $length; ++$i) {
            $digit = (int) $digits[$i];

            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    public function getErrorCode(): string
    {
        return 'financial.luhn';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} is not a valid card number.';
    }
}
