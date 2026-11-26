<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Brazilian;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates Brazilian CEP (Código de Endereçamento Postal).
 *
 * Accepts formatted (00000-000) or unformatted (00000000) input.
 * Validates format only — does not check range against Correios database.
 *
 * @package KaririCode\Validator\Rule\Brazilian
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class CepRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', $value);

        if ($digits === null || \strlen($digits) !== 8) {
            return false;
        }

        // CEP cannot be all zeros
        return $digits !== '00000000';
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'brazilian.cep';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} is not a valid CEP.';
    }
}
