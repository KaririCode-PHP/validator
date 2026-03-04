<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Type;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that a value is an instance of a given class/interface.
 *
 * Parameter: `class` (string) — FQCN of expected class/interface.
 *
 * @package KaririCode\Validator\Rule\Type
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class InstanceOfRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $className = (string) $context->getParameter('class', '');

        if ($className === '' || !is_object($value)) {
            return false;
        }

        return $value instanceof $className;
    }

    public function getErrorCode(): string
    {
        return 'type.instance_of';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must be an instance of {class}.';
    }
}
