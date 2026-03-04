<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates ULID format (Crockford Base32, 26 chars).
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class UlidRule implements ValidationRule
{
    private const PATTERN = '/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/';

    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match(self::PATTERN, strtoupper($value)) === 1;
    }

    public function getErrorCode(): string
    {
        return 'string.ulid';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be a valid ULID.';
    }
}
