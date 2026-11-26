<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\CrossField;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a field is present when another field has a value.
 *
 * Parameter: `other` (string) — field name to check presence of.
 *
 * @package KaririCode\Validator\Rule\CrossField
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class RequiredWithRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $otherParam = $context->getParameter('other', '');
        $otherField = \is_string($otherParam) ? $otherParam : '';
        $rootData = $context->getRootData();

        // If other field absent or empty, this rule doesn't apply
        if (! \array_key_exists($otherField, $rootData) || $this->isEmpty($rootData[$otherField])) {
            return true;
        }

        // Other field is present — this field must also be present
        return ! $this->isEmpty($value);
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (\is_string($value) && trim($value) === '') {
            return true;
        }

        return \is_array($value) && $value === [];
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'crossfield.required_with';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} is required when {other} is present.';
    }
}
