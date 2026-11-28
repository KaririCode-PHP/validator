<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\NumericValue;
use KaririCode\Validator\Result\Violation;

// ── Rule: LessThanOrEqualRule ────────────────────────────────────────
// A number less than or equal to a threshold.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts numbers less than or equal to 'threshold'. Integers, finite floats and
 * numeric strings are numbers; other values fail.
 *
 * Preconditions: 'threshold' is a number.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['quantity' => [['less_than_or_equal', ['threshold' => 0]]]]
 *
 * References: SPEC-002 §5.
 */
final readonly class LessThanOrEqualRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $threshold = $context->numberParameter('threshold');
        $number = NumericValue::of($value);

        return $number !== null && $number <= $threshold;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'comparison.less_than_or_equal', message: 'The {field} must be less than or equal to {threshold}.');
    }
}
