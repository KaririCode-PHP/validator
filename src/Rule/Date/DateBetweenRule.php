<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Date;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a date falls between two boundaries (exclusive).
 *
 * Parameters: `after` (string), `before` (string), `format` (string, default 'Y-m-d').
 *
 * @package KaririCode\Validator\Rule\Date
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class DateBetweenRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $format = (string) $context->getParameter('format', 'Y-m-d');
        $afterStr = $context->getParameter('after');
        $beforeStr = $context->getParameter('before');

        if ($afterStr === null || $beforeStr === null) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat($format, $value);
        $after = \DateTimeImmutable::createFromFormat($format, (string) $afterStr);
        $before = \DateTimeImmutable::createFromFormat($format, (string) $beforeStr);

        if ($date === false || $after === false || $before === false) {
            return false;
        }

        return $date > $after && $date < $before;
    }

    public function getErrorCode(): string
    {
        return 'date.between';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be a date between {after} and {before}.';
    }
}
