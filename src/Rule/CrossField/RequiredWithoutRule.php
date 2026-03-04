<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\CrossField;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a field is present when another field is absent.
 *
 * Parameter: `other` (string) — field name to check absence of.
 *
 * @package KaririCode\Validator\Rule\CrossField
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class RequiredWithoutRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $otherField = (string) $context->getParameter('other', '');
        $rootData = $context->getRootData();

        // If other field present and non-empty, this rule doesn't apply
        if (array_key_exists($otherField, $rootData) && !$this->isEmpty($rootData[$otherField])) {
            return true;
        }

        // Other field absent — this field must be present
        return !$this->isEmpty($value);
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_array($value) && $value === []) {
            return true;
        }

        return false;
    }

    public function getErrorCode(): string
    {
        return 'crossfield.required_without';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} is required when {other} is absent.';
    }
}
