<?php

declare(strict_types=1);

namespace KaririCode\Validator\Result;

// ── Result: ValidationError ──────────────────────────────────────────
// One failed rule on one field, with its message resolved.
// ─────────────────────────────────────────────────────────────────────

/**
 * Records that the value of a field broke a rule: the field as written in
 * the rule map, the rule's error code, the message in the configured
 * locale with its placeholders filled, and the parameters the message
 * was filled from.
 *
 * Preconditions: built by the engine; the message is plain text, and
 * whoever renders it into HTML escapes it.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $error->field;     // 'email'
 *     $error->errorCode; // 'string.email'
 *     $error->message;   // 'The email must be a valid email address.'
 *
 * References: ADR-002; SPEC-001 §6.
 */
final readonly class ValidationError
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public string $field,
        public string $errorCode,
        public string $message,
        public array $parameters = [],
    ) {}
}
