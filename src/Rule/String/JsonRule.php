<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a string is well-formed JSON.
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class JsonRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getErrorCode(): string
    {
        return 'string.json';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be valid JSON.';
    }
}
