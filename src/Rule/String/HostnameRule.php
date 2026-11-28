<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: HostnameRule ───────────────────────────────────────────────
// A DNS host name.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts dot-separated labels of 1 to 63 letters, digits and hyphens,
 * none starting or ending with a hyphen, 253 characters at most.
 * Internationalized names must be given in their ASCII (punycode) form.
 *
 * Preconditions: none; non-strings fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['host' => ['hostname']]
 *
 * References: RFC 1123 §2.1; RFC 1035 §2.3.4; SPEC-002 §2.
 */
final readonly class HostnameRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && strlen($value) <= 253 && preg_match('/\A(?!-)[A-Za-z0-9-]{1,63}(?<!-)(?:\.(?!-)[A-Za-z0-9-]{1,63}(?<!-))*\z/', $value) === 1;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.hostname', message: 'The {field} must be a valid hostname.');
    }
}
