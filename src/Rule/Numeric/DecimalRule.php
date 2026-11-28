<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Numeric;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\NumericValue;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: DecimalRule ────────────────────────────────────────────────
// A number with at most so many decimal places.
// ─────────────────────────────────────────────────────────────────────

/**
 * Counts the decimal places of the number as written, ignoring trailing
 * zeros and taking exponents into account ("1.50" has 1, "1.5e-3" has
 * 4); a float counts by its shortest exact representation. Integers have
 * none.
 *
 * Preconditions: 'places', when given, is 0 or more; 2 by default.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['price' => ['decimal'], 'rate' => [['decimal', ['places' => 4]]]]
 *
 * References: SPEC-002 §3.
 */
final readonly class DecimalRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $places = $this->places($context);
        $number = NumericValue::of($value);

        if ($number === null) {
            return false;
        }

        $parts = explode('e', strtolower(is_string($value) ? trim($value) : var_export($number, true)));
        $dot = strpos($parts[0], '.');
        $decimals = $dot === false ? 0 : strlen(rtrim(substr($parts[0], $dot + 1), '0'));

        return $decimals - (int) ($parts[1] ?? 0) <= $places;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'numeric.decimal', message: 'The {field} must be a number with at most {places} decimal places.', parameters: ['places' => $this->places($context)]);
    }

    private function places(ValidationContext $context): int
    {
        $places = $context->intParameter('places', 2);

        return $places >= 0 ? $places : throw InvalidRuleException::invalidParameter(field: $context->field, parameter: 'places', expected: '0 or more', given: (string) $places);
    }
}
