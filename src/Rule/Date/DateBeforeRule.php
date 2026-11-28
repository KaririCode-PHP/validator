<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Date;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\DateValue;
use KaririCode\Validator\Result\Violation;

// ── Rule: DateBeforeRule ─────────────────────────────────────────────
// A date earlier than a bound.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts a date strictly earlier than 'before'. The value is a string in
 * 'format' (Y-m-d unless set) or a DateTimeInterface; the bound is a
 * string in the same format or a DateTimeInterface. Unnamed fields
 * are zero, so a date is never earlier than itself.
 *
 * Preconditions: 'before' is a date in 'format' or a DateTimeInterface.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['ends_on' => [['date_before', ['before' => '2030-01-01']]]]
 *
 * References: PHP DateTimeImmutable::createFromFormat(); SPEC-002 §4.
 */
final readonly class DateBeforeRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $format = $context->stringParameter('format', 'Y-m-d');
        $bound = DateValue::bound(context: $context, name: 'before', format: $format);
        $date = DateValue::of($value, $format);

        return $date !== null && $date < $bound;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'date.before', message: 'The {field} must be a date before {before}.');
    }
}
