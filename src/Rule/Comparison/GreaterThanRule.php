<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a numeric value is strictly greater than a threshold.
 *
 * Parameter: `threshold` (int|float).
 *
 * @package KaririCode\Validator\Rule\Comparison
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class GreaterThanRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $threshold = $context->getParameter('threshold');

        return $threshold !== null && (float) $value > (float) $threshold;
    }

    public function getErrorCode(): string
    {
        return 'comparison.greater_than';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be greater than {threshold}.';
    }
}
