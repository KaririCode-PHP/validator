<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\NumericValue;
use KaririCode\Validator\Result\Violation;

// ── Rule: LessThanRule ───────────────────────────────────────────────
// A number less than a threshold.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts numbers less than 'threshold'. Integers, finite floats and
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
 *     ['quantity' => [['less_than', ['threshold' => 0]]]]
 *
 * References: SPEC-002 §5.
 */
final readonly class LessThanRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $threshold = $context->numberParameter('threshold');
        $number = NumericValue::of($value);

        return $number !== null && $number < $threshold;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'comparison.less_than', message: 'The {field} must be less than {threshold}.');
    }
}
