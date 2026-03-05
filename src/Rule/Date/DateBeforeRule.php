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
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        $format = $context->getParameter('format', 'Y-m-d');
        $format = \is_string($format) ? $format : 'Y-m-d';
        $beforeStr = $context->getParameter('before');

        if ($beforeStr === null) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat($format, $value);
        $before = (\is_string($beforeStr) ? \DateTimeImmutable::createFromFormat($format, $beforeStr) : false);

        if ($date === false || $before === false) {
            return false;
        }

        return $date < $before;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'date.before';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be a date before {before}.';
    }
}
