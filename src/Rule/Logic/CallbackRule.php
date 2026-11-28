<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use Closure;
use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: CallbackRule ───────────────────────────────────────────────
// A check written as a closure.
// ─────────────────────────────────────────────────────────────────────

/**
 * Wraps a closure that receives the value and the context and returns
 * true for a valid value. Anything other than true is a failure, so a
 * closure that returns 1 or "yes" fails closed. The error code and the
 * message are the application's.
 *
 * Preconditions: the closure has no side effects; it may run for every request.
 *
 * Thread-safety: stateless, if the closure is.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $registry->register('even', new CallbackRule(
 *         static fn (mixed $value): bool => is_int($value) && $value % 2 === 0,
 *         errorCode: 'custom.even',
 *         message: 'The {field} must be even.',
 *     ));
 *
 * References: SPEC-002 §1.
 */
final readonly class CallbackRule implements ValidationRule
{
    public function __construct(
        private Closure $callback,
        private string $errorCode = 'logic.callback',
        private string $message = 'The {field} is invalid.',
    ) {}

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return ($this->callback)($value, $context) === true;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: $this->errorCode, message: $this->message);
    }
}
