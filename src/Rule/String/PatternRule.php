<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: PatternRule ────────────────────────────────────────────────
// A string matching a regular expression.
// ─────────────────────────────────────────────────────────────────────

/**
 * Matches the value against 'pattern' with preg_match. A pattern that
 * does not compile is the application's mistake and throws; a value the
 * engine cannot match (invalid UTF-8 under /u, the backtracking limit)
 * fails, so a pathological input cannot pass.
 *
 * Preconditions: 'pattern' is a complete PCRE pattern with delimiters; anchor it with \A and \z.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['code' => [['pattern', ['pattern' => '/\A[A-Z]{3}-\d{4}\z/']]]]
 *
 * References: PHP PCRE; SPEC-002 §2.
 */
final readonly class PatternRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $pattern = $context->stringParameter('pattern');

        if ($pattern === '') {
            throw InvalidRuleException::invalidParameter(field: $context->field, parameter: 'pattern', expected: 'a regular expression');
        }

        if (!is_string($value)) {
            return false;
        }

        $matched = @preg_match($pattern, $value);

        if ($matched === false && preg_last_error() === PREG_INTERNAL_ERROR) {
            throw InvalidRuleException::invalidParameter(field: $context->field, parameter: 'pattern', expected: 'a regular expression that compiles');
        }

        return $matched === 1;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.pattern', message: 'The {field} format is invalid.');
    }
}
