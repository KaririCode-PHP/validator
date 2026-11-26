<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Date;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a string matches a given date format.
 *
 * Parameter: `format` (string, default 'Y-m-d').
 * Uses DateTimeImmutable::createFromFormat for strict parsing.
 *
 * @package KaririCode\Validator\Rule\Date
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class DateFormatRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        $format = $context->getParameter('format', 'Y-m-d');
        $format = \is_string($format) ? $format : 'Y-m-d';
        $date = \DateTimeImmutable::createFromFormat($format, $value);

        return $date !== false && $date->format($format) === $value;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'date.format';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must match the date format {format}.';
    }
}
