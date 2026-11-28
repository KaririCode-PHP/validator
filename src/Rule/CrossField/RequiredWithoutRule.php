<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\CrossField;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\Blank;
use KaririCode\Validator\Result\Violation;

// ── Rule: RequiredWithoutRule ────────────────────────────────────────
// Required when another field is not filled in.
// ─────────────────────────────────────────────────────────────────────

/**
 * Fails when neither this field nor the field named by 'other' is filled
 * in: one of two contacts, say.
 *
 * Preconditions: 'other' names a field.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['phone' => [['required_without', ['other' => 'email']]]]
 *
 * References: SPEC-002 §8.
 */
final readonly class RequiredWithoutRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $other = $context->stringParameter('other');

        return !Blank::is($context->fieldValue($other)) || !Blank::is($value);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'crossfield.required_without', message: 'The {field} is required when {other} is absent.');
    }
}
