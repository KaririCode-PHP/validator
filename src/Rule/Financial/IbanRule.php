<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Financial;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: IbanRule ───────────────────────────────────────────────────
// An IBAN whose check digits are right.
// ─────────────────────────────────────────────────────────────────────

/**
 * Ignores whitespace and case, requires a country code, two check digits
 * and 11 to 30 alphanumerics (15 to 34 characters in all), and checks
 * the ISO 7064 MOD 97-10 remainder. It does not check the national
 * length or format of each country.
 *
 * Preconditions: none; non-strings fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['account' => ['iban']]
 *
 * References: ISO 13616-1:2020; ISO/IEC 7064:2003 (MOD 97-10); SPEC-002 §9.
 */
final readonly class IbanRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $iban = strtoupper((string) preg_replace(pattern: '/\s+/', replacement: '', subject: $value));

        if (preg_match('/\A[A-Z]{2}\d{2}[A-Z0-9]{11,30}\z/', $iban) !== 1) {
            return false;
        }

        $remainder = 0;

        foreach (str_split(substr($iban, 4) . substr(string: $iban, offset: 0, length: 4)) as $character) {
            $number = ctype_alpha($character) ? ord($character) - 55 : $character;
            $remainder = (int) ($remainder . $number) % 97;
        }

        return $remainder === 1;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'financial.iban', message: 'The {field} is not a valid IBAN.');
    }
}
