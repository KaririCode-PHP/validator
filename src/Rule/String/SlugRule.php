<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;

/**
 * Validates URL slug format (lowercase, alphanumeric, hyphen-separated).
 *
 * @package KaririCode\Validator\Rule\String
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class SlugRule implements ValidationRule
{
    private const string PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return \is_string($value) && preg_match(self::PATTERN, $value) === 1;
    }

    #[\Override]
    public function getErrorCode(): string
    {
        return 'string.slug';
    }

    #[\Override]
    public function getDefaultMessage(): string
    {
        return 'The {field} must be a valid URL slug.';
    }
}
