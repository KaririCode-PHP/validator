<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates a string against a regular expression pattern.
 *
 * Parameter: `pattern` (string) — PCRE regex including delimiters.
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class PatternRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $pattern = $context->getParameter('pattern', '');

        if ($pattern === '' || $pattern === null) {
            return true;
        }

        return preg_match((string) $pattern, $value) === 1;
    }

    public function getErrorCode(): string
    {
        return 'string.pattern';
    }

    public function getDefaultMessage(): string
    {
        return 'The {field} format is invalid.';
    }
}
