<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\NumericValue;
use KaririCode\Validator\Result\Violation;

// ── Rule: BetweenRule ────────────────────────────────────────────────
// A number strictly between two bounds.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts numbers greater than 'min' and less than 'max', both
 * excluded; 'range' is the inclusive variant.
 *
 * Preconditions: 'min' and 'max' are numbers.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['ratio' => [['between', ['min' => 0, 'max' => 1]]]]
 *
 * References: SPEC-002 §5.
 */
final readonly class BetweenRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $min = $context->numberParameter('min');
        $max = $context->numberParameter('max');
        $number = NumericValue::of($value);

        return $number !== null && $number > $min && $number < $max;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'comparison.between', message: 'The {field} must be greater than {min} and less than {max}.');
    }
}
