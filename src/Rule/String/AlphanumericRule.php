<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a string contains only Unicode letters and digits.
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class AlphanumericRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && $value !== '' && preg_match('/^[\pL\pN]+$/u', $value) === 1;
    }

    public function getErrorCode(): string
    {
        return 'string.alphanumeric';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must contain only letters and numbers.';
    }
}
