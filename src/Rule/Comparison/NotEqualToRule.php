<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Comparison;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: NotEqualToRule ─────────────────────────────────────────────
// The value must differ from a given one.
// ─────────────────────────────────────────────────────────────────────

/**
 * Compares with !== against 'expected', so types matter: 1 differs from
 * "1".
 *
 * Preconditions: 'expected' is given, null included.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['username' => [['not_equal_to', ['expected' => 'admin']]]]
 *
 * References: SPEC-002 §5.
 */
final readonly class NotEqualToRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return $value !== $this->expected($context);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'comparison.not_equal_to', message: 'The {field} must not be equal to {expected}.');
    }

    private function expected(ValidationContext $context): mixed
    {
        return $context->hasParameter('expected') ? $context->parameter('expected') : throw InvalidRuleException::missingParameter(field: $context->field, parameter: 'expected');
    }
}
