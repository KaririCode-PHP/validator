<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\CrossField;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\Blank;
use KaririCode\Validator\Result\Violation;

// ── Rule: RequiredWithRule ───────────────────────────────────────────
// Required when another field is filled in.
// ─────────────────────────────────────────────────────────────────────

/**
 * Fails when the field named by 'other' is filled in and this one is
 * not, "filled in" meaning what 'required' accepts.
 *
 * Preconditions: 'other' names a field.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['state' => [['required_with', ['other' => 'country']]]]
 *
 * References: SPEC-002 §8.
 */
final readonly class RequiredWithRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $other = $context->stringParameter('other');

        return Blank::is($context->fieldValue($other)) || !Blank::is($value);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'crossfield.required_with', message: 'The {field} is required when {other} is present.');
    }
}
