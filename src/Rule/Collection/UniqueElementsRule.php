<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Collection;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that all elements in an array are unique.
 *
 * Uses strict comparison for element uniqueness checking.
 *
 * @package KaririCode\Validator\Rule\Collection
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class UniqueElementsRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $serialized = array_map(
            static fn (mixed $item): string => serialize($item),
            $value,
        );

        return count($serialized) === count(array_unique($serialized));
    }

    public function getErrorCode(): string
    {
        return 'collection.unique';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must contain only unique values.';
    }
}
