<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Financial;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates International Bank Account Number (IBAN) format and check digits.
 *
 * Implements ISO 13616-1:2020 validation (MOD 97-10 check).
 * Zero external dependencies — pure PHP bcmath.
 *
 * References:
 * - ISO 13616-1:2020 (IBAN structure)
 * - ECBS "IBAN: International Bank Account Number" (2003)
 *
 * @package KaririCode\Validator\Rule\Financial
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class IbanRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $iban = strtoupper(preg_replace('/\s+/', '', $value) ?? '');

        if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{4,30}$/', $iban)) {
            return false;
        }

        // Move first 4 chars to end
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        // Convert letters to numbers (A=10, B=11, ..., Z=35)
        $numeric = '';
        for ($i = 0, $len = strlen($rearranged); $i < $len; ++$i) {
            $char = $rearranged[$i];
            $numeric .= ctype_alpha($char)
                ? (string) (ord($char) - 55)
                : $char;
        }

        // MOD 97-10 check using bcmath or manual long division
        if (function_exists('bcmod')) {
            return bcmod($numeric, '97') === '1';
        }

        // Fallback: chunked modulo for systems without bcmath
        return $this->mod97($numeric) === 1;
    }

    private function mod97(string $numeric): int
    {
        $remainder = 0;

        for ($i = 0, $len = strlen($numeric); $i < $len; ++$i) {
            $remainder = ($remainder * 10 + (int) $numeric[$i]) % 97;
        }

        return $remainder;
    }

    public function getErrorCode(): string
    {
        return 'financial.iban';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} is not a valid IBAN.';
    }
}
