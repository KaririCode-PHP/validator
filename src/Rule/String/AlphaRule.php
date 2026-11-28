<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: AlphaRule ──────────────────────────────────────────────────
// Letters only, in any script.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts a non-empty string made only of Unicode letters (general
 * category L): "José" and "Ωmega" pass; "abc1", "a b", "" and a
 * trailing newline fail.
 *
 * Preconditions: none; non-strings and invalid UTF-8 fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['name' => ['alpha']]
 *
 * References: Unicode TR #18 (property L); SPEC-002 §2.
 */
final readonly class AlphaRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match('/\A\pL+\z/u', $value) === 1;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.alpha', message: 'The {field} must contain only letters.');
    }
}
