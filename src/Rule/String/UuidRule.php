<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates UUID format (v1-v7, RFC 4122).
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class UuidRule implements ValidationRule
{
    private const string PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return \is_string($value) && preg_match(self::PATTERN, $value) === 1;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'string.uuid';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be a valid UUID.';
    }
}
