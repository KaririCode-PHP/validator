<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a numeric value falls within exclusive bounds (min < value < max).
 *
 * Parameters: `min` (int|float), `max` (int|float).
 *
 * @package KaririCode\Validator\Rule\Comparison
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class BetweenRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $numericValue = (float) $value;
        $min = $context->getParameter('min');
        $max = $context->getParameter('max');

        if ($min === null || $max === null) {
            return false;
        }

        return $numericValue > (float) $min && $numericValue < (float) $max;
    }

    public function getErrorCode(): string
    {
        return 'comparison.between';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be between {min} and {max}.';
    }
}
