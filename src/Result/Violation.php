<?php

declare(strict_types=1);

namespace KaririCode\Validator\Result;

// ── Result: Violation ────────────────────────────────────────────────
// What a rule reports when a value breaks it.
// ─────────────────────────────────────────────────────────────────────

/**
 * Describes a failed check before it is attached to a field: the error
 * code that selects the message, the English message the rule falls back
 * to, and placeholder values the rule computed (a default it applied, a
 * bound it compared against). The engine adds the rule's parameters and
 * the field name, resolves the message and records a ValidationError.
 *
 * Preconditions: the code is non-empty and stable, because message
 * catalogs and clients key on it; placeholders in the message are written
 * {name}.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     return new Violation(code: 'string.length_min', message: 'The {field} must be at least {min} characters.');
 *
 * References: ADR-005; SPEC-001 §6.
 */
final readonly class Violation
{
    /**
     * @param array<string, mixed> $parameters placeholder values beyond the rule's own parameters
     */
    public function __construct(
        public string $code,
        public string $message,
        public array $parameters = [],
    ) {}
}
