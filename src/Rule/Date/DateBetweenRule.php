<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Date;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\DateValue;
use KaririCode\Validator\Result\Violation;

// ── Rule: DateBetweenRule ────────────────────────────────────────────
// A date strictly between two bounds.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts a date later than 'after' and earlier than 'before', both
 * excluded, read as in date_after and date_before.
 *
 * Preconditions: 'after' and 'before' are dates in 'format' or DateTimeInterface instances.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['travel_on' => [['date_between', ['after' => '2026-12-01', 'before' => '2027-01-31']]]]
 *
 * References: SPEC-002 §4.
 */
final readonly class DateBetweenRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $format = $context->stringParameter('format', 'Y-m-d');
        $after = DateValue::bound(context: $context, name: 'after', format: $format);
        $before = DateValue::bound(context: $context, name: 'before', format: $format);
        $date = DateValue::of($value, $format);

        return $date !== null && $date > $after && $date < $before;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'date.between', message: 'The {field} must be a date between {after} and {before}.');
    }
}
