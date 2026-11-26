<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a value is strictly equal to the expected value.
 *
 * Parameter: `expected` (mixed).
 *
 * @package KaririCode\Validator\Rule\Comparison
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class EqualToRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return $value === $context->getParameter('expected');
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'comparison.equal_to';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be equal to {expected}.';
    }
}
