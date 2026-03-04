<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Type;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a value matches the expected PHP type.
 *
 * Parameter: `type` (string) — expected type name.
 * Supports: string, int, float, bool, array, object, null.
 *
 * @package KaririCode\Validator\Rule\Type
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class TypeRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $expectedType = (string) $context->getParameter('type', 'string');

        return match ($expectedType) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'null' => $value === null,
            'numeric' => is_numeric($value),
            'scalar' => is_scalar($value),
            'callable' => is_callable($value),
            default => false,
        };
    }

    public function getErrorCode(): string
    {
        return 'type.mismatch';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be of type {type}.';
    }
}
