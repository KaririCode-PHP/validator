<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: JsonRule ───────────────────────────────────────────────────
// A string that is valid JSON.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts any JSON text json_validate() accepts (objects, arrays and
 * scalars, nested up to 512 levels) without decoding it.
 *
 * Preconditions: none; non-strings fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['payload' => ['json']]
 *
 * References: RFC 8259; PHP json_validate(); SPEC-002 §2.
 */
final readonly class JsonRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && json_validate($value);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.json', message: 'The {field} must be valid JSON.');
    }
}
