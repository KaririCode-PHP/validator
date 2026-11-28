<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Brazilian;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: CpfRule ────────────────────────────────────────────────────
// A CPF with valid check digits.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts 11 digits, bare or as 000.000.000-00, whose two check digits
 * match the Receita Federal modulo 11 algorithm. Numbers made of one
 * repeated digit, which pass the arithmetic, fail.
 *
 * Preconditions: none; non-strings and other formats fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['cpf' => ['cpf']]
 *
 * References: Receita Federal do Brasil, CPF check digit algorithm (modulo 11); SPEC-002 §10.
 */
final readonly class CpfRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value) || preg_match('/\A\d{3}\.?\d{3}\.?\d{3}-?\d{2}\z/', $value) !== 1) {
            return false;
        }

        $digits = str_replace(search: ['.', '-'], replace: '', subject: $value);

        return preg_match('/\A(\d)\1{10}\z/', $digits) !== 1
            && self::checkDigit($digits, 9) === (int) $digits[9]
            && self::checkDigit($digits, 10) === (int) $digits[10];
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'brazilian.cpf', message: 'The {field} is not a valid CPF.');
    }

    private static function checkDigit(string $digits, int $length): int
    {
        $sum = 0;

        for ($index = 0; $index < $length; ++$index) {
            $sum += (ord($digits[$index]) - 48) * ($length + 1 - $index);
        }

        return $sum * 10 % 11 % 10;
    }
}
