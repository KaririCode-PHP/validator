<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a value is present and non-empty.
 *
 * Considers null, empty string, and empty array as "not present".
 * Zero (0) and false are considered present (they are valid values).
 *
 * @package KaririCode\Validator\Rule\Logic
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class RequiredRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if ($value === null) {
            return false;
        }

        if (\is_string($value) && trim($value) === '') {
            return false;
        }

        return ! (\is_array($value) && $value === []);
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'logic.required';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} field is required.';
    }
}
