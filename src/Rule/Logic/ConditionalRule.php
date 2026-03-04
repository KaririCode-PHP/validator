<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates only when a precondition is met.
 *
 * Wraps another rule, executing it only if the condition callable
 * returns true. Useful for conditional requirements:
 * "required if country is BR".
 *
 * @package KaririCode\Validator\Rule\Logic
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ConditionalRule implements ValidationRule
{
    /**
     * @param \Closure(mixed, ValidationContext): bool $condition  Precondition
     * @param ValidationRule                           $innerRule  Rule to apply when condition is met
     */
    public function __construct(
        private \Closure $condition,
        private ValidationRule $innerRule,
    ) {
    }

    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!($this->condition)($value, $context)) {
            return true; // Condition not met — skip validation
        }

        return $this->innerRule->validate($value, $context);
    }

    public function getErrorCode(): string
    {
        return $this->innerRule->getErrorCode();
    }

    public function getDefaultMessage(): string
    {
        return $this->innerRule->getDefaultMessage();
    }
}
