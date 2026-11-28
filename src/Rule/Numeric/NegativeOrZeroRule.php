<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Numeric;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\NumericValue;
use KaririCode\Validator\Result\Violation;

// ── Rule: NegativeOrZeroRule ─────────────────────────────────────────
// A number zero or less.
// ─────────────────────────────────────────────────────────────────────

/**
 * Zero or less. Integers, finite floats and numeric strings are numbers;
 * booleans, INF and NAN are not, so they fail.
 *
 * Preconditions: none.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['amount' => ['negative_or_zero']]
 *
 * References: SPEC-002 §3.
 */
final readonly class NegativeOrZeroRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $number = NumericValue::of($value);

        return $number !== null && $number <= 0;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'numeric.negative_or_zero', message: 'The {field} must be negative or zero.');
    }
}
