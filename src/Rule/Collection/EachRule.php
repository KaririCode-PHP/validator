<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Collection;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates each element of an array against an inner rule.
 *
 * Applies a single ValidationRule to every element. For multi-rule
 * element validation, wrap with CompositeRule.
 *
 * @package KaririCode\Validator\Rule\Collection
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class EachRule implements ValidationRule
{
    public function __construct(
        private ValidationRule $innerRule,
    ) {
    }

    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $element) {
            if (!$this->innerRule->validate($element, $context)) {
                return false;
            }
        }

        return true;
    }

    public function getErrorCode(): string
    {
        return 'collection.each';
    }

    public function getDefaultMessage(): string
    {
        return 'Each item in {field} must be valid.';
    }
}
