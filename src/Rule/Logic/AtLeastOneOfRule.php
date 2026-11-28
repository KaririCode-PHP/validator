<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: AtLeastOneOfRule ───────────────────────────────────────────
// Passes when any of several rules passes.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts the value when at least one inner rule accepts it, such as a
 * tax id that may be a CPF or a CNPJ.
 *
 * Preconditions: at least one inner rule.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $registry->register('cpf_or_cnpj', new AtLeastOneOfRule(new CpfRule(), new CnpjRule()));
 *
 * References: SPEC-002 §1.
 */
final readonly class AtLeastOneOfRule implements ValidationRule
{
    /** @var array<array-key, ValidationRule> */
    private array $rules;

    public function __construct(ValidationRule ...$rules)
    {
        if ($rules === []) {
            throw InvalidRuleException::noRules('AtLeastOneOfRule');
        }

        $this->rules = $rules;
    }

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return array_any($this->rules, static fn(ValidationRule $rule): bool => $rule->validate($value, $context));
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'logic.at_least_one_of', message: 'The {field} must pass at least one of its rules.');
    }
}
