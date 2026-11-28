<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Collection;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: UniqueElementsRule ─────────────────────────────────────────
// An array without repeated items.
// ─────────────────────────────────────────────────────────────────────

/**
 * Fails when two items are identical: scalars and arrays compare by
 * type and value (1, "1" and 1.0 are three items), objects by identity.
 *
 * Preconditions: none; non-arrays fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['emails' => ['unique']]
 *
 * References: SPEC-002 §7.
 */
final readonly class UniqueElementsRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $keys = array_map(static fn(mixed $item): int|string => is_object($item) ? spl_object_id($item) : serialize($item), $value);

        return count(array_unique($keys)) === count($keys);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'collection.unique', message: 'The {field} must contain only unique values.');
    }
}
