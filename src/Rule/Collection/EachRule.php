<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Collection;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: EachRule ───────────────────────────────────────────────────
// Every item of an array must pass a rule.
// ─────────────────────────────────────────────────────────────────────

/**
 * Applies the inner rule to each item, with the field's context, and
 * fails with one error for the field when any item fails. An empty
 * array passes; add 'count' to require items.
 *
 * Preconditions: none; non-arrays fail.
 *
 * Thread-safety: stateless, if the inner rule is.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['emails' => [new EachRule(new EmailRule())]]
 *
 * References: SPEC-002 §7.
 */
final readonly class EachRule implements ValidationRule
{
    public function __construct(
        private ValidationRule $innerRule,
    ) {}

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_array($value) && array_all($value, fn(mixed $item): bool => $this->innerRule->validate($item, $context));
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'collection.each', message: 'Each item in {field} must be valid.');
    }
}
