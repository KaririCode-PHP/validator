<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Date;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a date is after a given boundary.
 *
 * Parameters: `after` (string, date), `format` (string, default 'Y-m-d').
 *
 * @package KaririCode\Validator\Rule\Date
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class DateAfterRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        $format = $context->getParameter('format', 'Y-m-d');
        $format = \is_string($format) ? $format : 'Y-m-d';
        $afterStr = $context->getParameter('after');

        if ($afterStr === null) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat($format, $value);
        if (! \is_string($afterStr)) {
            return false;
        }
        $after = \DateTimeImmutable::createFromFormat($format, $afterStr);

        if ($date === false || $after === false) {
            return false;
        }

        return $date > $after;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'date.after';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be a date after {after}.';
    }
}
