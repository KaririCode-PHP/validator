<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a value is one of the allowed choices.
 *
 * @package KaririCode\Validator\Rule\Logic
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ChoiceRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        /** @var array<mixed> $choices */
        $choices = $context->getParameter('choices', []);

        if ($choices === []) {
            return true;
        }

        return in_array($value, $choices, strict: true);
    }

    public function getErrorCode(): string
    {
        return 'logic.choice';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} value is not a valid choice.';
    }
}
