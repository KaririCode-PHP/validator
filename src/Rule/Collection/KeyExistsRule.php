<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Collection;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates that an array contains a specific key.
 *
 * Parameter: `key` (string).
 *
 * @package KaririCode\Validator\Rule\Collection
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class KeyExistsRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $key = $context->getParameter('key');

        if ($key === null) {
            return false;
        }

        return array_key_exists((string) $key, $value);
    }

    public function getErrorCode(): string
    {
        return 'collection.key_exists';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} must contain the key {key}.';
    }
}
