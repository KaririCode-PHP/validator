<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\CrossField;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a field value differs from another field.
 *
 * Parameter: `other` (string) — field name to compare against.
 *
 * @package KaririCode\Validator\Rule\CrossField
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class DifferentFromRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $otherField = (string) $context->getParameter('other', '');
        $rootData = $context->getRootData();

        if (!array_key_exists($otherField, $rootData)) {
            return true; // Other field absent — no conflict
        }

        return $value !== $rootData[$otherField];
    }

    public function getErrorCode(): string
    {
        return 'crossfield.different_from';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must differ from {other}.';
    }
}
