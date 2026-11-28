<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: CompositeRule ──────────────────────────────────────────────
// Several rules reported as one.
// ─────────────────────────────────────────────────────────────────────

/**
 * Passes when every inner rule passes, and reports one error with its own
 * code and message instead of one per inner rule: a "strong password"
 * built from length, pattern and not_empty fails as one requirement.
 *
 * Preconditions: at least one inner rule.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $strong = new CompositeRule('custom.strong_password', 'The {field} is too weak.', new LengthRule(), new PatternRule());
 *     $engine->validate($data, ['password' => [[$strong, ['min' => 12, 'pattern' => '/\d/']]]]);
 *
 * References: SPEC-002 §1.
 */
final readonly class CompositeRule implements ValidationRule
{
    /** @var array<array-key, ValidationRule> */
    private array $rules;

    public function __construct(
        private string $errorCode,
        private string $message,
        ValidationRule ...$rules,
    ) {
        if ($rules === []) {
            throw InvalidRuleException::noRules('CompositeRule');
        }

        $this->rules = $rules;
    }

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return array_all($this->rules, static fn(ValidationRule $rule): bool => $rule->validate($value, $context));
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: $this->errorCode, message: $this->message);
    }
}
