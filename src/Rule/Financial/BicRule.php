<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Financial;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: BicRule ────────────────────────────────────────────────────
// A BIC (SWIFT code) in 8 or 11 characters.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts a party prefix of 4 letters, a country code of 2 letters, a
 * suffix of 2 letters or digits and an optional branch of 3, ignoring
 * case and surrounding whitespace. It does not check the country code
 * against ISO 3166.
 *
 * Preconditions: none; non-strings fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['swift' => ['bic']]
 *
 * References: ISO 9362:2022; SPEC-002 §9.
 */
final readonly class BicRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match('/\A[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}(?:[A-Z0-9]{3})?\z/', strtoupper(trim($value))) === 1;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'financial.bic', message: 'The {field} is not a valid BIC/SWIFT code.');
    }
}
