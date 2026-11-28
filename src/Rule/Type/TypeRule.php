<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Type;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: TypeRule ───────────────────────────────────────────────────
// The value must have a PHP type.
// ─────────────────────────────────────────────────────────────────────

/**
 * Checks the value with the is_* function of 'type': string, int
 * (integer), float (double), bool (boolean), array, object, null,
 * numeric, scalar or callable. An unknown type name is the application's
 * mistake and throws.
 *
 * Preconditions: 'type' is one of the names above.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['tags' => [['type', ['type' => 'array']]]]
 *
 * References: PHP type functions; SPEC-002 §6.
 */
final readonly class TypeRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $type = $context->stringParameter('type');

        return match ($type) {
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
            default => throw InvalidRuleException::invalidParameter(field: $context->field, parameter: 'type', expected: 'string, int, float, bool, array, object, null, numeric, scalar or callable', given: var_export($type, true)),
        };
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'type.mismatch', message: 'The {field} must be of type {type}.');
    }
}
