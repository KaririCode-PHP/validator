<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Numeric;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a numeric value falls within min/max bounds.
 *
 * Parameters: `min` (int|float|null), `max` (int|float|null).
 * Both bounds are inclusive.
 *
 * @package KaririCode\Validator\Rule\Numeric
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class RangeRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        $numericValue = (float) $value;
        $min = $context->getParameter('min');
        $max = $context->getParameter('max');

        if ($min !== null && is_numeric($min) && $numericValue < (float) $min) {
            return false;
        }

        if ($max !== null && is_numeric($max) && $numericValue > (float) $max) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'numeric.range';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be between {min} and {max}.';
    }
}
