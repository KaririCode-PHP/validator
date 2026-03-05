<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a string contains only Unicode letters.
 *
 * Uses \pL (Letter) Unicode category for full i18n support.
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class AlphaRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return \is_string($value) && $value !== '' && preg_match('/^\pL+$/u', $value) === 1;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'string.alpha';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must contain only letters.';
    }
}
