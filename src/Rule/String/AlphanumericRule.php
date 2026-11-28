<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: AlphanumericRule ───────────────────────────────────────────
// Letters and digits only, in any script.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts a non-empty string made only of Unicode letters and numbers
 * (general categories L and N); spaces, punctuation and a trailing
 * newline fail.
 *
 * Preconditions: none; non-strings and invalid UTF-8 fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['username' => ['alphanumeric']]
 *
 * References: Unicode TR #18 (properties L and N); SPEC-002 §2.
 */
final readonly class AlphanumericRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match('/\A[\pL\pN]+\z/u', $value) === 1;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.alphanumeric', message: 'The {field} must contain only letters and numbers.');
    }
}
