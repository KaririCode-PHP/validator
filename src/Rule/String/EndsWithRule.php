<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: EndsWithRule ───────────────────────────────────────────────
// A string ending with a suffix.
// ─────────────────────────────────────────────────────────────────────

/**
 * Compares bytes, so the check is case-sensitive.
 *
 * Preconditions: 'suffix' is a non-empty string.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['email' => [['ends_with', ['suffix' => '@example.com']]]]
 *
 * References: SPEC-002 §2.
 */
final readonly class EndsWithRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $suffix = $this->affix($context, 'suffix');

        return is_string($value) && str_ends_with($value, $suffix);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.ends_with', message: 'The {field} must end with {suffix}.');
    }

    private function affix(ValidationContext $context, string $name): string
    {
        $affix = $context->stringParameter($name);

        return $affix !== '' ? $affix : throw InvalidRuleException::invalidParameter(field: $context->field, parameter: $name, expected: 'a non-empty string');
    }
}
