<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Brazilian;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: CnpjRule ───────────────────────────────────────────────────
// A CNPJ, numeric or alphanumeric, with valid check digits.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts the numeric CNPJ and the alphanumeric one the Receita Federal
 * issues from July 2026: twelve characters of digits or upper-case
 * letters A–Z and two numeric check digits, bare or as
 * AA.AAA.AAA/AAAA-00. Each character counts its ASCII code minus 48
 * (A=17 … Z=42) in the modulo 11 check, which leaves numeric CNPJs
 * unchanged. All 26 letters are accepted, as the RFB format defines; the
 * RFB withholds some letter combinations when issuing, which is not a
 * validation rule. Fourteen identical characters fail.
 *
 * Preconditions: none; non-strings, lower-case letters and other formats fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['cnpj' => ['cnpj']]; // '11.222.333/0001-81' and '12.ABC.345/01DE-35' pass
 *
 * References: IN RFB nº 2.229/2024; RFB, Perguntas e Respostas sobre o CNPJ alfanumérico (questions 1, 2 and 14); NT Conjunta DF-e 2025.001 (pattern [A-Z0-9]{12}[0-9]{2}); SPEC-002 §10.
 */
final readonly class CnpjRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value) || preg_match('/\A[0-9A-Z]{2}\.?[0-9A-Z]{3}\.?[0-9A-Z]{3}\/?[0-9A-Z]{4}-?\d{2}\z/', $value) !== 1) {
            return false;
        }

        $cnpj = str_replace(search: ['.', '/', '-'], replace: '', subject: $value);

        return preg_match('/\A(.)\1{13}\z/', $cnpj) !== 1
            && self::checkDigit(substr(string: $cnpj, offset: 0, length: 12)) === (int) $cnpj[12]
            && self::checkDigit(substr(string: $cnpj, offset: 0, length: 13)) === (int) $cnpj[13];
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'brazilian.cnpj', message: 'The {field} is not a valid CNPJ.');
    }

    private static function checkDigit(string $base): int
    {
        $sum = 0;
        $weight = strlen($base) - 7;

        foreach (str_split($base) as $character) {
            $sum += (ord($character) - 48) * $weight;
            $weight = $weight === 2 ? 9 : $weight - 1;
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
