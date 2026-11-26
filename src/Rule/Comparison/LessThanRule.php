<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a numeric value is strictly less than a threshold.
 *
 * Parameter: `threshold` (int|float).
 *
 * @package KaririCode\Validator\Rule\Comparison
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class LessThanRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        $threshold = $context->getParameter('threshold');

        return $threshold !== null && (float) $value < (float) (is_numeric($threshold) ? $threshold : 0);
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'comparison.less_than';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be less than {threshold}.';
    }
}
