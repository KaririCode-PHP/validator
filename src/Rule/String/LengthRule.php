<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates string length within min/max bounds.
 *
 * Uses mb_strlen() for multibyte-safe length measurement.
 * Parameters: `min` (int, default 0), `max` (int|null, default null).
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class LengthRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $length = mb_strlen($value, 'UTF-8');
        $min = (int) $context->getParameter('min', 0);
        $max = $context->getParameter('max');

        if ($length < $min) {
            return false;
        }

        if ($max !== null && $length > (int) $max) {
            return false;
        }

        return true;
    }

    public function getErrorCode(): string
    {
        return 'string.length';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be between {min} and {max} characters.';
    }
}
