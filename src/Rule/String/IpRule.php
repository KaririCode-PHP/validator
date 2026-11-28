<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: IpRule ─────────────────────────────────────────────────────
// An IPv4 or IPv6 address.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts what FILTER_VALIDATE_IP accepts, of the 'version' asked for (4
 * or 6) or of either. Private and reserved ranges are addresses too.
 *
 * Preconditions: 'version', when given, is 4 or 6.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['server' => [['ip', ['version' => 4]]]]
 *
 * References: RFC 791; RFC 4291 §2.2; PHP FILTER_VALIDATE_IP; SPEC-002 §2.
 */
final readonly class IpRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        /** @var mixed $version */
        $version = $context->parameter('version');
        $flags = match ($version) {
            null => FILTER_FLAG_NONE,
            4, '4' => FILTER_FLAG_IPV4,
            6, '6' => FILTER_FLAG_IPV6,
            default => throw InvalidRuleException::invalidParameter(field: $context->field, parameter: 'version', expected: '4 or 6', given: is_scalar($version) ? var_export($version, true) : get_debug_type($version)),
        };

        return is_string($value) && filter_var(value: $value, filter: FILTER_VALIDATE_IP, options: $flags) !== false;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.ip', message: 'The {field} must be a valid IP address.');
    }
}
