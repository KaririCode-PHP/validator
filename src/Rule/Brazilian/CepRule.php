<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Brazilian;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: CepRule ────────────────────────────────────────────────────
// A CEP (Brazilian postal code).
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts 8 digits, bare or as 00000-000, except 00000-000. It checks
 * the format, not whether the Correios assigned the code.
 *
 * Preconditions: none; non-strings and other formats fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['cep' => ['cep']]
 *
 * References: Correios, Código de Endereçamento Postal; SPEC-002 §10.
 */
final readonly class CepRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match('/\A\d{5}-?\d{3}\z/', $value) === 1 && str_replace(search: '-', replace: '', subject: $value) !== '00000000';
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'brazilian.cep', message: 'The {field} is not a valid CEP.');
    }
}
