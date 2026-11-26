<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Collection;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates array/countable element count within min/max bounds.
 *
 * Parameters: `min` (int, default 0), `max` (int|null).
 *
 * @package KaririCode\Validator\Rule\Collection
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class CountRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_array($value) && ! $value instanceof \Countable) {
            return false;
        }

        $count = \count($value);
        $minParam = $context->getParameter('min', 0);
        $min = is_numeric($minParam) ? (int) $minParam : 0;
        $maxParam = $context->getParameter('max');

        if ($count < $min) {
            return false;
        }

        if ($maxParam !== null && is_numeric($maxParam) && $count > (int) $maxParam) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'collection.count';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must contain between {min} and {max} items.';
    }
}
