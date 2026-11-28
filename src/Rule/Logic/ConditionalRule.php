<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use Closure;
use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: ConditionalRule ────────────────────────────────────────────
// Applies a rule only when a condition holds.
// ─────────────────────────────────────────────────────────────────────

/**
 * Runs the inner rule only when the condition closure returns true for
 * the value and the context (for example, another field's value);
 * otherwise it passes. When the inner rule fails, its own error is
 * reported.
 *
 * Preconditions: the condition has no side effects.
 *
 * Thread-safety: stateless, if the condition and the inner rule are.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $cnpjForCompanies = new ConditionalRule(
 *         static fn (mixed $value, ValidationContext $context): bool => $context->fieldValue('type') === 'company',
 *         new CnpjRule(),
 *     );
 *
 * References: SPEC-002 §1.
 */
final readonly class ConditionalRule implements ValidationRule
{
    public function __construct(
        private Closure $condition,
        private ValidationRule $innerRule,
    ) {}

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return ($this->condition)($value, $context) !== true || $this->innerRule->validate($value, $context);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return $this->innerRule->violation($context);
    }
}
