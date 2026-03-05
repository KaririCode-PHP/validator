<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Numeric;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a value is an integer or integer-like string.
 *
 * @package KaririCode\Validator\Rule\Numeric
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class IntegerRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'numeric.integer';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be an integer.';
    }
}
