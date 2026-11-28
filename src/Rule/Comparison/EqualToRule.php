<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: EqualToRule ────────────────────────────────────────────────
// The value must be identical to an expected one.
// ─────────────────────────────────────────────────────────────────────

/**
 * Compares with === against 'expected', so types matter: 1 is not "1".
 *
 * Preconditions: 'expected' is given, null included.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['terms' => [['equal_to', ['expected' => true]]]]
 *
 * References: SPEC-002 §5.
 */
final readonly class EqualToRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return $value === $this->expected($context);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'comparison.equal_to', message: 'The {field} must be equal to {expected}.');
    }

    private function expected(ValidationContext $context): mixed
    {
        return $context->hasParameter('expected') ? $context->parameter('expected') : throw InvalidRuleException::missingParameter(field: $context->field, parameter: 'expected');
    }
}
