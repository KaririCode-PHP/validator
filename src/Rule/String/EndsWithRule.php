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
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        $suffix = ($context->getParameter('suffix', '') ?? '');

        return \is_string($suffix) && str_ends_with($value, $suffix);
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'string.ends_with';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must end with {suffix}.';
    }
}
