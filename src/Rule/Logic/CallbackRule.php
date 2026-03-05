<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\Logic;

use KaririCode\Validator\Contract\{ValidationContext, ValidationRule};

/**
 * Validates using a user-supplied callable.
 *
 * Escape hatch for ad-hoc validation without creating a class:
 * ```php
 * new CallbackRule(fn($v) => $v % 2 === 0, 'logic.even', 'Must be even')
 * ```
 *
 * @package KaririCode\Validator\Rule\Logic
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class CallbackRule implements ValidationRule
{
    /**
     * @param \Closure(mixed, ValidationContext): bool $callback  Validation callable
     * @param string                                   $errorCode Custom error code
     * @param string                                   $message   Custom error message
     */
    public function __construct(
        private \Closure $callback,
        private string $errorCode = 'logic.callback',
        private string $message = 'The {field} is invalid.',
    ) {
    }

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return ($this->callback)($value, $context);
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
