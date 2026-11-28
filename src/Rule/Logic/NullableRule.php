<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: NullableRule ───────────────────────────────────────────────
// Lets a field be null.
// ─────────────────────────────────────────────────────────────────────

/**
 * Marks a field as optional: when its value is null (or the field is
 * absent), the engine skips the field's other rules; otherwise they run
 * as usual. The rule itself always passes, so where it sits in the list
 * does not matter.
 *
 * Preconditions: none.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['middle_name' => ['nullable', ['length', ['max' => 100]]]]
 *
 * References: SPEC-002 §1.
 */
final readonly class NullableRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return true;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'logic.nullable', message: 'The {field} must be null or valid.');
    }
}
