<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Date;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\DateValue;
use KaririCode\Validator\Result\Violation;

// ── Rule: DateFormatRule ─────────────────────────────────────────────
// A date written exactly in a format.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts a string that parses in 'format' (Y-m-d unless set) to a real
 * date and prints back identically, so "2026-2-5", "2026-02-30" and
 * extra characters fail.
 *
 * Preconditions: 'format', when given, uses DateTimeImmutable::createFromFormat() letters.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['birthday' => ['date_format'], 'starts_at' => [['date_format', ['format' => 'Y-m-d H:i']]]]
 *
 * References: ISO 8601-1:2019 (the default format); PHP DateTimeImmutable::createFromFormat(); SPEC-002 §4.
 */
final readonly class DateFormatRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $format = $context->stringParameter('format', 'Y-m-d');

        if (!is_string($value)) {
            return false;
        }

        return DateValue::parse($value, $format)?->format($format) === $value;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'date.format', message: 'The {field} must be a date in the format {format}.', parameters: ['format' => $context->stringParameter('format', 'Y-m-d')]);
    }
}
