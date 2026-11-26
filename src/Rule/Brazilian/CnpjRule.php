<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Brazilian;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates Brazilian CNPJ (Cadastro Nacional da Pessoa Jurídica).
 *
 * Supports both formats:
 *  - Legacy numeric  (IN RFB nº 2.119/2022): 14 digits, e.g. 11.222.333/0001-81
 *  - Alphanumeric    (IN RFB nº 2.229/2024): 12 alphanumeric chars + 2 numeric
 *    check digits, e.g. AB.3D4.5GH/JK00-41  (effective July 2026)
 *
 * Algorithm — Módulo 11 (both formats):
 *   Each character's numeric value is: digit → digit itself; letter → ord(A-Z) - 48
 *   (A=17, B=18, … Z=42). The same weights and check-digit logic apply.
 *
 * Forbidden characters in alphanumeric CNPJs: I, O, Q (visual ambiguity with
 * digits 1, 0, 0) per Nota Técnica Conjunta RFB nº 2025.001.
 *
 * References:
 *  - Instrução Normativa RFB nº 2.229, 15/10/2024
 *  - Nota Técnica Conjunta nº 2025.001
 *
 * @package KaririCode\Validator\Rule\Brazilian
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.2.0 ARFA 1.3
 */
final readonly class CnpjRule implements ValidationRule
{
    /**
     * Characters forbidden in the alphanumeric CNPJ body (positions 1–12).
     * I, O, Q cause visual ambiguity; excluded per official spec.
     */
    private const array FORBIDDEN = ['I', 'O', 'Q'];

    /** Weights for the first check digit calculation. */
    private const array WEIGHTS_1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    /** Weights for the second check digit calculation. */
    private const array WEIGHTS_2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        // Strip formatting characters: dots, slashes, hyphens
        $cnpj = preg_replace('/[.\-\/]/', '', strtoupper($value));

        if ($cnpj === null || \strlen($cnpj) !== 14) {
            return false;
        }

        // Check digits (positions 13–14) must always be numeric
        if (! ctype_digit(substr($cnpj, 12, 2))) {
            return false;
        }

        // Body (positions 1–12) must be digits or A–Z, without forbidden chars
        $body = substr($cnpj, 0, 12);
        if (! $this->isValidBody($body)) {
            return false;
        }

        // Reject uniform sequences (all same character), e.g. "000000000000"
        if (\strlen(count_chars($body, 3)) === 1 && $body[0] === $cnpj[12] && $body[0] === $cnpj[13]) {
            return false;
        }

        // Módulo 11 — first check digit
        $firstCheck = $this->computeCheckDigit($body, self::WEIGHTS_1);
        if ((int) $cnpj[12] !== $firstCheck) {
            return false;
        }

        // Módulo 11 — second check digit
        $secondCheck = $this->computeCheckDigit($body . $cnpj[12], self::WEIGHTS_2);

        return (int) $cnpj[13] === $secondCheck;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'brazilian.cnpj';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} is not a valid CNPJ.';
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Validates the 12-character body: only [A-Z0-9] excluding forbidden letters.
     */
    private function isValidBody(string $body): bool
    {
        for ($i = 0; $i < 12; ++$i) {
            $c = $body[$i];
            if (! ctype_alnum($c)) {
                return false;
            }
            if (\in_array($c, self::FORBIDDEN, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converts a single character to its numeric value for Módulo 11:
     *   - digit  → (int) digit
     *   - letter → ord(char) - 48  (A=17, B=18, … Z=42)
     */
    private function charNumericValue(string $c): int
    {
        return ctype_alpha($c) ? \ord($c) - 48 : (int) $c;
    }

    /**
     * Computes a single Módulo 11 check digit from an input string and weights.
     *
     * @param array<int, int> $weights Weight multipliers
     * Returns 0 when remainder < 2, otherwise 11 - remainder.
     */
    /**
     * @param array<int, int> $weights
     */
    private function computeCheckDigit(string $input, array $weights): int
    {
        $sum = 0;
        $len = \strlen($input);

        for ($i = 0; $i < $len; ++$i) {
            $sum += $this->charNumericValue($input[$i]) * $weights[$i];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
