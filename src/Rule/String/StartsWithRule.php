<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: StartsWithRule ─────────────────────────────────────────────
// A string beginning with a prefix.
// ─────────────────────────────────────────────────────────────────────

/**
 * Compares bytes, so the check is case-sensitive.
 *
 * Preconditions: 'prefix' is a non-empty string.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['phone' => [['starts_with', ['prefix' => '+55']]]]
 *
 * References: SPEC-002 §2.
 */
final readonly class StartsWithRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $prefix = $this->affix($context, 'prefix');

        return is_string($value) && str_starts_with($value, $prefix);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.starts_with', message: 'The {field} must start with {prefix}.');
    }

    private function affix(ValidationContext $context, string $name): string
    {
        $affix = $context->stringParameter($name);

        return $affix !== '' ? $affix : throw InvalidRuleException::invalidParameter(field: $context->field, parameter: $name, expected: 'a non-empty string');
    }
}
