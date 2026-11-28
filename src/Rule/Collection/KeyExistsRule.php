<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Collection;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: KeyExistsRule ──────────────────────────────────────────────
// An array that has a key.
// ─────────────────────────────────────────────────────────────────────

/**
 * Checks array_key_exists() for 'key', so a key holding null exists.
 * PHP's key rules apply: "1" and 1 are the same key.
 *
 * Preconditions: 'key' is an integer or a string.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['options' => [['key_exists', ['key' => 'mode']]]]
 *
 * References: SPEC-002 §7.
 */
final readonly class KeyExistsRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $key = $context->hasParameter('key') ? $context->parameter('key') : throw InvalidRuleException::missingParameter(field: $context->field, parameter: 'key');

        if (!is_int($key) && !is_string($key)) {
            throw InvalidRuleException::invalidParameter(field: $context->field, parameter: 'key', expected: 'an integer or a string', given: get_debug_type($key));
        }

        return is_array($value) && array_key_exists($key, $value);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'collection.key_exists', message: 'The {field} must contain the key {key}.');
    }
}
