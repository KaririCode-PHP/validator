<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Numeric;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a value is a decimal number with a maximum number of decimal places.
 *
 * Parameter: `places` (int, default 2).
 *
 * @package KaririCode\Validator\Rule\Numeric
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class DecimalRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        $placesParam = $context->getParameter('places', 2);
        $places = is_numeric($placesParam) ? (int) $placesParam : 2;
        $stringValue = (string) $value;
        $dotPos = strpos($stringValue, '.');

        if ($dotPos === false) {
            return true; // Integer — zero decimal places
        }

        $decimalPart = substr($stringValue, $dotPos + 1);

        return \strlen(rtrim($decimalPart, '0')) <= $places;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'numeric.decimal';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be a decimal number with at most {places} decimal places.';
    }
}
