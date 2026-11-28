<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\Blank;
use KaririCode\Validator\Result\Violation;

// ── Rule: RequiredRule ───────────────────────────────────────────────
// The value must be filled in.
// ─────────────────────────────────────────────────────────────────────

/**
 * Fails when the value is missing: null, a string of whitespace only, or
 * an empty array. Zero, "0" and false are values, so they pass.
 *
 * Preconditions: none.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['name' => ['required']]
 *
 * References: SPEC-002 §1.
 */
final readonly class RequiredRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return !Blank::is($value);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'logic.required', message: 'The {field} field is required.');
    }
}
