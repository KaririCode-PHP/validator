<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: ChoiceRule ─────────────────────────────────────────────────
// The value must be one of a fixed list.
// ─────────────────────────────────────────────────────────────────────

/**
 * Passes when the value is identical (===) to one of the 'choices', so
 * 1 and "1" are different choices.
 *
 * Preconditions: 'choices' is a non-empty list.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['status' => [['choice', ['choices' => ['draft', 'published']]]]]
 *
 * References: SPEC-002 §1.
 */
final readonly class ChoiceRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $choices = $context->arrayParameter('choices');

        if ($choices === []) {
            throw InvalidRuleException::invalidParameter(field: $context->field, parameter: 'choices', expected: 'a non-empty list');
        }

        return in_array(needle: $value, haystack: $choices, strict: true);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'logic.choice', message: 'The {field} value is not a valid choice.');
    }
}
