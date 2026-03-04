<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Date;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a date is before a given boundary.
 *
 * Parameters: `before` (string, date), `format` (string, default 'Y-m-d').
 *
 * @package KaririCode\Validator\Rule\Date
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class DateBeforeRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $format = (string) $context->getParameter('format', 'Y-m-d');
        $beforeStr = $context->getParameter('before');

        if ($beforeStr === null) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat($format, $value);
        $before = \DateTimeImmutable::createFromFormat($format, (string) $beforeStr);

        if ($date === false || $before === false) {
            return false;
        }

        return $date < $before;
    }

    public function getErrorCode(): string
    {
        return 'date.before';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be a date before {before}.';
    }
}
