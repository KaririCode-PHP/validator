<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Numeric;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\NumericValue;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: RangeRule ──────────────────────────────────────────────────
// A number within inclusive bounds.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts numbers from 'min' to 'max', both included; with one bound
 * only, the other side is open and the message names only that bound.
 * Integers are compared as integers.
 *
 * Preconditions: 'min' or 'max', or both, as numbers.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['age' => [['range', ['min' => 18, 'max' => 130]]]]
 *
 * References: SPEC-002 §3.
 */
final readonly class RangeRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        [$min, $max] = $this->bounds($context);
        $number = NumericValue::of($value);

        return $number !== null && ($min === null || $number >= $min) && ($max === null || $number <= $max);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        [$min, $max] = $this->bounds($context);

        if ($max === null) {
            return new Violation(code: 'numeric.range_min', message: 'The {field} must be at least {min}.');
        }

        return $min === null
            ? new Violation(code: 'numeric.range_max', message: 'The {field} must be at most {max}.')
            : new Violation(code: 'numeric.range', message: 'The {field} must be between {min} and {max}.');
    }

    /**
     * @return array{int|float|null, int|float|null}
     */
    private function bounds(ValidationContext $context): array
    {
        $min = $context->hasParameter('min') ? $context->numberParameter('min') : null;
        $max = $context->hasParameter('max') ? $context->numberParameter('max') : null;

        if ($min === null && $max === null) {
            throw InvalidRuleException::missingBounds($context->field);
        }

        return [$min, $max];
    }
}
