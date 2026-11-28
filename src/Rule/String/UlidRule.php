<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: UlidRule ───────────────────────────────────────────────────
// A ULID in Crockford base32.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts 26 Crockford base32 characters in either case (no I, L, O,
 * U), the first at most 7 so the timestamp fits 48 bits.
 *
 * Preconditions: none; non-strings fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['id' => ['ulid']]
 *
 * References: ULID specification (github.com/ulid/spec); SPEC-002 §2.
 */
final readonly class UlidRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match('/\A[0-7][0-9A-HJKMNP-TV-Z]{25}\z/', strtoupper($value)) === 1;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.ulid', message: 'The {field} must be a valid ULID.');
    }
}
