<?php
declare(strict_types=1);
namespace KaririCode\Validator\Rule\Logic;
use KaririCode\Validator\Contract\{ValidationContext, ValidationRule};

/**
 * Passes if value is null; otherwise delegates to inner rules via context.
 *
 * Wraps another rule: null values are accepted, non-null values
 * must satisfy the composed validation.
 *
 * @package KaririCode\Validator\Rule\Logic
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class NullableRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return $value === null;
    }

    public function getErrorCode(): string { return 'logic.nullable'; }
    public function getDefaultMessage(): string { return 'The {field} must be null or valid.'; }
}
