<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use DateTimeImmutable;
use DateTimeInterface;
use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Exception\InvalidRuleException;

// ── Core: DateValue ──────────────────────────────────────────────────
// Parses a date in a format, without rolling invalid dates over.
// ─────────────────────────────────────────────────────────────────────

/**
 * Reads a date written in a DateTime format, strictly: fields the format
 * does not name are zero instead of the current time, so two dates of
 * the same day compare equal, and a date PHP would otherwise move
 * (February 30, hour 25) is not a date. The date rules read their values
 * and their bounds through it; a DateTimeInterface is taken as it is.
 *
 * Preconditions: the format uses DateTimeImmutable::createFromFormat()
 * letters.
 *
 * Thread-safety: stateless; it reads the last parse errors right after
 * its own call.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     DateValue::parse('2026-02-30', 'Y-m-d'); // null
 *     DateValue::parse('2026-02-28', 'Y-m-d'); // 2026-02-28 00:00:00
 *
 * References: PHP manual, DateTimeImmutable::createFromFormat() ("!"
 * resets unparsed fields); SPEC-002 §4.
 */
final class DateValue
{
    public static function parse(string $value, string $format): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!' . $format, $value);

        return $date !== false && DateTimeImmutable::getLastErrors() === false ? $date : null;
    }

    /**
     * A value as a date: a string written in the format, or a
     * DateTimeInterface; null for anything else.
     */
    public static function of(mixed $value, string $format): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return is_string($value) ? self::parse($value, $format) : null;
    }

    /**
     * The bound a date rule compares with, which the application must
     * give as a date.
     */
    public static function bound(ValidationContext $context, string $name, string $format): DateTimeImmutable
    {
        /** @var mixed $bound */
        $bound = $context->hasParameter($name) ? $context->parameter($name) : throw InvalidRuleException::missingParameter(field: $context->field, parameter: $name);

        return self::of($bound, $format) ?? throw InvalidRuleException::invalidParameter(
            field: $context->field,
            parameter: $name,
            expected: "a date in the format {$format} or a DateTimeInterface",
            given: is_string($bound) ? var_export($bound, true) : get_debug_type($bound),
        );
    }
}
