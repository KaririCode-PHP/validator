<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Date;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\DateValue;
use KaririCode\Validator\Result\Violation;

// ── Rule: DateAfterRule ──────────────────────────────────────────────
// A date later than a bound.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts a date strictly later than 'after'. The value is a string in
 * 'format' (Y-m-d unless set) or a DateTimeInterface; the bound is a
 * string in the same format or a DateTimeInterface. Unnamed fields
 * are zero, so a date is never later than itself.
 *
 * Preconditions: 'after' is a date in 'format' or a DateTimeInterface.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['ends_on' => [['date_after', ['after' => '2030-01-01']]]]
 *
 * References: PHP DateTimeImmutable::createFromFormat(); SPEC-002 §4.
 */
final readonly class DateAfterRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $format = $context->stringParameter('format', 'Y-m-d');
        $bound = DateValue::bound(context: $context, name: 'after', format: $format);
        $date = DateValue::of($value, $format);

        return $date !== null && $date > $bound;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'date.after', message: 'The {field} must be a date after {after}.');
    }
}
