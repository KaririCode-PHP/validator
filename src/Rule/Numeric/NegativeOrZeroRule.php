<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Numeric;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a numeric value is zero or negative (<= 0).
 *
 * @package KaririCode\Validator\Rule\Numeric
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class NegativeOrZeroRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_numeric($value) && (float) $value <= 0;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'numeric.negative_or_zero';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be negative or zero.';
    }
}
