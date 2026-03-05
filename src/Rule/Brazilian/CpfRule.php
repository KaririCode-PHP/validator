<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Brazilian;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates Brazilian CPF (Cadastro de Pessoas Físicas).
 *
 * Implements the Receita Federal check-digit algorithm (Módulo 11).
 * Accepts formatted (000.000.000-00) or unformatted (00000000000) input.
 * Zero external dependencies.
 *
 * References:
 * - Receita Federal do Brasil — Instrução Normativa RFB nº 1.548/2015
 *
 * @package KaririCode\Validator\Rule\Brazilian
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class CpfRule implements ValidationRule
{
    /** Known-invalid sequences where all digits are the same. */
    private const array BLACKLIST = [
        '00000000000', '11111111111', '22222222222', '33333333333',
        '44444444444', '55555555555', '66666666666', '77777777777',
        '88888888888', '99999999999',
    ];

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', $value);

        if ($digits === null || \strlen($digits) !== 11) {
            return false;
        }

        if (\in_array($digits, self::BLACKLIST, true)) {
            return false;
        }

        // First check digit
        $sum = 0;
        for ($i = 0; $i < 9; ++$i) {
            $sum += (int) $digits[$i] * (10 - $i);
        }
        $remainder = ($sum * 10) % 11;
        $firstCheck = $remainder === 10 ? 0 : $remainder;

        if ((int) $digits[9] !== $firstCheck) {
            return false;
        }

        // Second check digit
        $sum = 0;
        for ($i = 0; $i < 10; ++$i) {
            $sum += (int) $digits[$i] * (11 - $i);
        }
        $remainder = ($sum * 10) % 11;
        $secondCheck = $remainder === 10 ? 0 : $remainder;

        return (int) $digits[10] === $secondCheck;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'brazilian.cpf';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} is not a valid CPF.';
    }
}
