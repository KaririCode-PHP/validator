<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a string ends with a given suffix.
 *
 * Parameter: `suffix` (string).
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class EndsWithRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $suffix = (string) $context->getParameter('suffix', '');

        return str_ends_with($value, $suffix);
    }

    public function getErrorCode(): string
    {
        return 'string.ends_with';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must end with {suffix}.';
    }
}
