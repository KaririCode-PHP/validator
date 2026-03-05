<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\CrossField;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a field value matches another field (e.g. password confirmation).
 *
 * Parameter: `other` (string) — field name to compare against.
 * Uses getRootData() from context for cross-field access.
 *
 * @package KaririCode\Validator\Rule\CrossField
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class SameAsRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $otherParam = $context->getParameter('other', '');
        $otherField = \is_string($otherParam) ? $otherParam : '';
        $rootData = $context->getRootData();
        if (! \array_key_exists($otherField, $rootData)) {
            return false;
        }

        return $value === $rootData[$otherField];
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'crossfield.same_as';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must match {other}.';
    }
}
