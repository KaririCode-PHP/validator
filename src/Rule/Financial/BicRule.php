<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Financial;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates BIC/SWIFT code format (ISO 9362).
 *
 * Format: 4 letters (bank) + 2 letters (country) + 2 alphanum (location)
 * + optional 3 alphanum (branch, "XXX" = primary office).
 *
 * @package KaririCode\Validator\Rule\Financial
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class BicRule implements ValidationRule
{
    private const PATTERN = '/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/';

    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match(self::PATTERN, strtoupper(trim($value))) === 1;
    }

    public function getErrorCode(): string
    {
        return 'financial.bic';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} is not a valid BIC/SWIFT code.';
    }
}
