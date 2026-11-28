<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Collection;

use Countable;
use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: CountRule ──────────────────────────────────────────────────
// An array or Countable with a size within bounds.
// ─────────────────────────────────────────────────────────────────────

/**
 * Counts the items of an array or Countable and checks them against
 * 'min' and 'max', both inclusive; with one bound only, the message
 * names only that bound.
 *
 * Preconditions: 'min' or 'max', or both, as integers.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['tags' => [['count', ['min' => 1, 'max' => 10]]]]
 *
 * References: SPEC-002 §7.
 */
final readonly class CountRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        [$min, $max] = $this->bounds($context);

        if (!is_array($value) && !$value instanceof Countable) {
            return false;
        }

        $count = count($value);

        return ($min === null || $count >= $min) && ($max === null || $count <= $max);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        [$min, $max] = $this->bounds($context);

        if ($max === null) {
            return new Violation(code: 'collection.count_min', message: 'The {field} must contain at least {min} items.');
        }

        return $min === null
            ? new Violation(code: 'collection.count_max', message: 'The {field} must contain at most {max} items.')
            : new Violation(code: 'collection.count', message: 'The {field} must contain between {min} and {max} items.');
    }

    /**
     * @return array{?int, ?int}
     */
    private function bounds(ValidationContext $context): array
    {
        $min = $context->hasParameter('min') ? $context->intParameter('min') : null;
        $max = $context->hasParameter('max') ? $context->intParameter('max') : null;

        if ($min === null && $max === null) {
            throw InvalidRuleException::missingBounds($context->field);
        }

        return [$min, $max];
    }
}
