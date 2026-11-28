<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: NotEmptyRule ───────────────────────────────────────────────
// A string with something other than whitespace.
// ─────────────────────────────────────────────────────────────────────

/**
 * Fails for non-strings and for strings that are empty or whitespace
 * only (spaces, tabs, line breaks, NUL, vertical tab).
 *
 * Preconditions: none.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['comment' => ['not_empty']]
 *
 * References: SPEC-002 §2.
 */
final readonly class NotEmptyRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.not_empty', message: 'The {field} must not be empty.');
    }
}
