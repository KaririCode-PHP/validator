<?php

declare(strict_types=1);

namespace KaririCode\Validator\Contract;

use KaririCode\Validator\Result\Violation;

// ── Contract: ValidationRule ─────────────────────────────────────────
// One check a value passes or fails.
// ─────────────────────────────────────────────────────────────────────

/**
 * Checks one value and, when the value fails, describes the failure: an
 * error code, an English message with {placeholders}, and the values the
 * placeholders take. Rules hold no state between calls, so one instance
 * serves every field and every request; whatever varies per use comes in
 * the context's parameters.
 *
 * Preconditions: validate() receives any value, including null; a rule
 * returns false for the types it does not handle instead of throwing.
 * violation() is called only after validate() returned false, with the
 * same context.
 *
 * Thread-safety: implementations must be stateless.
 *
 * Side effects: none; a rule never changes the value.
 *
 * Example:
 *
 *     final readonly class EvenRule implements ValidationRule
 *     {
 *         public function validate(mixed $value, ValidationContext $context): bool
 *         {
 *             return is_int($value) && $value % 2 === 0;
 *         }
 *
 *         public function violation(ValidationContext $context): Violation
 *         {
 *             return new Violation(code: 'custom.even', message: 'The {field} must be even.');
 *         }
 *     }
 *
 * References: ADR-001; SPEC-001 §3.
 */
interface ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool;

    public function violation(ValidationContext $context): Violation;
}
