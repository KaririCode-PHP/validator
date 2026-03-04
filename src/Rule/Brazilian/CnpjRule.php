<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Brazilian;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates Brazilian CNPJ (Cadastro Nacional da Pessoa Jurídica).
 *
 * Implements the Receita Federal check-digit algorithm.
 * Accepts formatted (00.000.000/0000-00) or unformatted input.
 *
 * References:
 * - Receita Federal do Brasil — Instrução Normativa RFB nº 2.119/2022
 *
 * @package KaririCode\Validator\Rule\Brazilian
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class CnpjRule implements ValidationRule
{
    private const BLACKLIST = [
        '00000000000000', '11111111111111', '22222222222222', '33333333333333',
        '44444444444444', '55555555555555', '66666666666666', '77777777777777',
        '88888888888888', '99999999999999',
    ];

    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', $value);

        if ($digits === null || strlen($digits) !== 14) {
            return false;
        }

        if (in_array($digits, self::BLACKLIST, true)) {
            return false;
        }

        // First check digit — weights: 5,4,3,2,9,8,7,6,5,4,3,2
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; ++$i) {
            $sum += (int) $digits[$i] * $weights1[$i];
        }
        $remainder = $sum % 11;
        $firstCheck = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $digits[12] !== $firstCheck) {
            return false;
        }

        // Second check digit — weights: 6,5,4,3,2,9,8,7,6,5,4,3,2
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; ++$i) {
            $sum += (int) $digits[$i] * $weights2[$i];
        }
        $remainder = $sum % 11;
        $secondCheck = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $digits[13] === $secondCheck;
    }

    public function getErrorCode(): string
    {
        return 'brazilian.cnpj';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} is not a valid CNPJ.';
    }
}
