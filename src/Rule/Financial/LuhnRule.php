<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Financial;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: LuhnRule ───────────────────────────────────────────────────
// A number whose Luhn check digit is right.
// ─────────────────────────────────────────────────────────────────────

/**
 * Strips spaces and hyphens, then requires 8 digits or more and a Luhn
 * sum divisible by 10: card numbers, IMEIs. It does not identify the
 * card brand or issuer.
 *
 * Preconditions: none; strings and integers are read.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['card' => ['luhn']]
 *
 * References: ISO/IEC 7812-1:2017 Annex B; SPEC-002 §9.
 */
final readonly class LuhnRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value) && !is_int($value)) {
            return false;
        }

        $digits = str_replace(search: [' ', '-'], replace: '', subject: (string) $value);

        if (strlen($digits) < 8 || !ctype_digit($digits)) {
            return false;
        }

        $sum = 0;
        $parity = strlen($digits) % 2;

        foreach (str_split($digits) as $index => $digit) {
            $product = (ord($digit) - 48) * ($index % 2 === $parity ? 2 : 1);
            $sum += $product > 9 ? $product - 9 : $product;
        }

        return $sum % 10 === 0;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'financial.luhn', message: 'The {field} is not a valid card number.');
    }
}
