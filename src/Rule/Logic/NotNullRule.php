<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\{ValidationContext, ValidationRule};

/**
 * Validates that a value is not null.
 *
 * Unlike RequiredRule, this accepts empty strings and empty arrays.
 *
 * @package KaririCode\Validator\Rule\Logic
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class NotNullRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return $value !== null;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'logic.not_null';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must not be null.';
    }
}
