<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates email format using PHP's FILTER_VALIDATE_EMAIL.
 *
 * Delegates to filter_var() which implements RFC 5322 validation.
 * For stricter DNS-based validation, compose with a DnsCheckRule.
 *
 * References:
 * - RFC 5322: Internet Message Format
 * - PHP Manual: filter_var, FILTER_VALIDATE_EMAIL
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class EmailRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getErrorCode(): string
    {
        return 'string.email';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be a valid email address.';
    }
}
