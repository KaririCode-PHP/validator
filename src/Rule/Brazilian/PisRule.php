<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Brazilian;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: PisRule ────────────────────────────────────────────────────
// A PIS/PASEP/NIT with a valid check digit.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts 11 digits, bare or as 000.00000.00-0, whose check digit
 * matches the modulo 11 algorithm with weights 3, 2, 9 … 2. Numbers made
 * of one repeated digit fail.
 *
 * Preconditions: none; non-strings and other formats fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['pis' => ['pis']]
 *
 * References: Caixa Econômica Federal, PIS/PASEP check digit (modulo 11); SPEC-002 §10.
 */
final readonly class PisRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value) || preg_match('/\A\d{3}\.?\d{5}\.?\d{2}-?\d\z/', $value) !== 1) {
            return false;
        }

        $digits = str_replace(search: ['.', '-'], replace: '', subject: $value);

        if (preg_match('/\A(\d)\1{10}\z/', $digits) === 1) {
            return false;
        }

        $sum = 0;

        foreach ([3, 2, 9, 8, 7, 6, 5, 4, 3, 2] as $index => $weight) {
            $sum += (ord($digits[$index]) - 48) * $weight;
        }

        $remainder = $sum % 11;

        return (int) $digits[10] === ($remainder < 2 ? 0 : 11 - $remainder);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'brazilian.pis', message: 'The {field} is not a valid PIS/PASEP.');
    }
}
