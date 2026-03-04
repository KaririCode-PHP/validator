<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a string starts with a given prefix.
 *
 * Parameter: `prefix` (string).
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class StartsWithRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $prefix = (string) $context->getParameter('prefix', '');

        return str_starts_with($value, $prefix);
    }

    public function getErrorCode(): string
    {
        return 'string.starts_with';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must start with {prefix}.';
    }
}
