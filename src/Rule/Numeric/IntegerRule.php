<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Numeric;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: IntegerRule ────────────────────────────────────────────────
// An integer, or a string or float that is one.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts what FILTER_VALIDATE_INT accepts: integers, integral floats
 * (12.0) and decimal integer strings, with optional sign and surrounding
 * whitespace. Booleans, "1e3", "012" and values beyond PHP_INT_MAX fail.
 *
 * Preconditions: none.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['quantity' => ['integer']]
 *
 * References: PHP FILTER_VALIDATE_INT; SPEC-002 §3.
 */
final readonly class IntegerRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return !is_bool($value) && filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'numeric.integer', message: 'The {field} must be an integer.');
    }
}
