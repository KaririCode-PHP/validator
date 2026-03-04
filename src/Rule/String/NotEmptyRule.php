<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a string is non-empty after trimming whitespace.
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class NotEmptyRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return trim($value) !== '';
    }

    public function getErrorCode(): string
    {
        return 'string.not_empty';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must not be empty.';
    }
}
