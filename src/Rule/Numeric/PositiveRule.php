<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Numeric;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\NumericValue;
use KaririCode\Validator\Result\Violation;

// ── Rule: PositiveRule ───────────────────────────────────────────────
// A number greater than zero.
// ─────────────────────────────────────────────────────────────────────

/**
 * Greater than zero. Integers, finite floats and numeric strings are numbers;
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
 *     ['amount' => ['positive']]
 *
 * References: SPEC-002 §3.
 */
final readonly class PositiveRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $number = NumericValue::of($value);

        return $number !== null && $number > 0;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'numeric.positive', message: 'The {field} must be a positive number.');
    }
}
