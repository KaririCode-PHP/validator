<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\CrossField;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: SameAsRule ─────────────────────────────────────────────────
// The value must be identical to another field.
// ─────────────────────────────────────────────────────────────────────

/**
 * Compares with === against the field named by 'other' (a dotted path
 * allowed); when that field is absent, the rule fails.
 *
 * Preconditions: 'other' names a field.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['password_confirmation' => [['same_as', ['other' => 'password']]]]
 *
 * References: SPEC-002 §8.
 */
final readonly class SameAsRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $other = $context->stringParameter('other');

        return $context->hasField($other) && $value === $context->fieldValue($other);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'crossfield.same_as', message: 'The {field} must match {other}.');
    }
}
