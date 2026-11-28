<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: UuidRule ───────────────────────────────────────────────────
// A UUID in its canonical text form.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts 36 characters, 8-4-4-4-12 hexadecimal digits in either case,
 * of any version and variant, the nil and max UUIDs included.
 *
 * Preconditions: none; non-strings fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['id' => ['uuid']]
 *
 * References: RFC 9562 §4; SPEC-002 §2.
 */
final readonly class UuidRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/i', $value) === 1;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.uuid', message: 'The {field} must be a valid UUID.');
    }
}
