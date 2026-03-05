<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * AND-composes multiple rules into a single rule.
 *
 * All inner rules must pass for the composite to pass. Follows the
 * Composite Pattern (GoF) applied to the Specification Pattern.
 *
 * @package KaririCode\Validator\Rule\Logic
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class CompositeRule implements ValidationRule
{
    /** @var array<ValidationRule> */
    private array $rules;

    public function __construct(
        private string $errorCode,
        private string $message,
        ValidationRule ...$rules,
    ) {
        $this->rules = $rules;
    }

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return array_all($this->rules, fn ($rule) => $rule->validate($value, $context));
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return $this->message;
    }
}
