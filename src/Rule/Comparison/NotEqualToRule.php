<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a value is not strictly equal to the rejected value.
 *
 * Parameter: `expected` (mixed).
 *
 * @package KaririCode\Validator\Rule\Comparison
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class NotEqualToRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return $value !== $context->getParameter('expected');
    }

    public function getErrorCode(): string
    {
        return 'comparison.not_equal_to';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must not be equal to {expected}.';
    }
}
