<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Type;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: InstanceOfRule ─────────────────────────────────────────────
// The value must be an instance of a class or interface.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts objects that are instances of 'class'. A class, interface or
 * enum that does not exist is the application's mistake and throws
 * instead of failing every value.
 *
 * Preconditions: 'class' names an existing class, interface or enum.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['when' => [['instance_of', ['class' => DateTimeInterface::class]]]]
 *
 * References: SPEC-002 §6.
 */
final readonly class InstanceOfRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $class = $context->stringParameter('class');

        if (!class_exists($class) && !interface_exists($class) && !enum_exists($class)) {
            throw InvalidRuleException::invalidParameter(field: $context->field, parameter: 'class', expected: 'an existing class, interface or enum', given: var_export($class, true));
        }

        return $value instanceof $class;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'type.instance_of', message: 'The {field} must be an instance of {class}.');
    }
}
