<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: NotNullRule ────────────────────────────────────────────────
// The value must not be null.
// ─────────────────────────────────────────────────────────────────────

/**
 * Fails only for null; an empty string or array passes. Use 'required'
 * to reject those too.
 *
 * Preconditions: none.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['middle_name' => ['not_null']]
 *
 * References: SPEC-002 §1.
 */
final readonly class NotNullRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return $value !== null;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'logic.not_null', message: 'The {field} must not be null.');
    }
}
