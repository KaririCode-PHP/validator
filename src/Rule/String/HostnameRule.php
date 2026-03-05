<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates RFC 952/1123 hostname format.
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class HostnameRule implements ValidationRule
{
    private const string PATTERN = '/^(?!-)[A-Za-z0-9-]{1,63}(?<!-)(\.[A-Za-z0-9-]{1,63})*$/';

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_string($value) || $value === '' || mb_strlen($value) > 253) {
            return false;
        }

        return preg_match(self::PATTERN, $value) === 1;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'string.hostname';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be a valid hostname.';
    }
}
