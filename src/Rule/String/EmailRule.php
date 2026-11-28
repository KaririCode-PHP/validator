<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: EmailRule ──────────────────────────────────────────────────
// An email address, as PHP validates them.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts the addresses FILTER_VALIDATE_EMAIL accepts: RFC 5321 syntax
 * with ASCII local parts, 254 characters at most (the longest address an
 * SMTP path carries; the filter enforces it). It checks syntax only: no
 * DNS lookup, no mailbox.
 *
 * Preconditions: none; non-strings fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['email' => ['required', 'email']]
 *
 * References: RFC 5321 §4.1.2 and §4.5.3.1.3; PHP FILTER_VALIDATE_EMAIL; SPEC-002 §2.
 */
final readonly class EmailRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.email', message: 'The {field} must be a valid email address.');
    }
}
