<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates IP address (v4, v6, or both).
 *
 * Parameter: `version` (4, 6, or null for both).
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class IpRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        $version = $context->getParameter('version');
        $flags = match ($version) {
            4, '4' => FILTER_FLAG_IPV4,
            6, '6' => FILTER_FLAG_IPV6,
            default => 0,
        };

        return filter_var($value, FILTER_VALIDATE_IP, $flags) !== false;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'string.ip';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be a valid IP address.';
    }
}
