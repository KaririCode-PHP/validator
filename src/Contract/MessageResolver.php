<?php

declare(strict_types=1);

namespace KaririCode\Validator\Contract;

// ── Contract: MessageResolver ────────────────────────────────────────
// Turns an error code into the message a person reads.
// ─────────────────────────────────────────────────────────────────────

/**
 * Picks the message for an error code in a locale and fills its
 * {placeholders} from the parameters; when it has no message for the
 * code, it fills the fallback the rule wrote. Plug in a translator here.
 *
 * Preconditions: parameters map placeholder names to values; values that
 * are not scalars, lists of scalars or dates are left out.
 *
 * Thread-safety: implementations decide; the default one is immutable.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $message = $resolver->resolve(errorCode: 'string.email', fallback: 'The {field} must be a valid email address.', parameters: ['field' => 'email']);
 *
 * References: ADR-005.
 */
interface MessageResolver
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function resolve(string $errorCode, string $fallback, array $parameters = [], ?string $locale = null): string;
}
