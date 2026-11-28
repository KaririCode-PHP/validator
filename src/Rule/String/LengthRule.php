<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: LengthRule ─────────────────────────────────────────────────
// A string length in characters, within bounds.
// ─────────────────────────────────────────────────────────────────────

/**
 * Counts characters, not bytes (mb_strlen, UTF-8), and checks them
 * against 'min' and 'max', both inclusive. With only one bound, the
 * message names only that bound.
 *
 * Preconditions: 'min' or 'max', or both, as integers.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['password' => [['length', ['min' => 12]]], 'name' => [['length', ['min' => 2, 'max' => 100]]]]
 *
 * References: SPEC-002 §2.
 */
final readonly class LengthRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        [$min, $max] = $this->bounds($context);

        if (!is_string($value)) {
            return false;
        }

        $length = mb_strlen($value, 'UTF-8');

        return ($min === null || $length >= $min) && ($max === null || $length <= $max);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        [$min, $max] = $this->bounds($context);

        if ($max === null) {
            return new Violation(code: 'string.length_min', message: 'The {field} must be at least {min} characters.');
        }

        return $min === null
            ? new Violation(code: 'string.length_max', message: 'The {field} must be at most {max} characters.')
            : new Violation(code: 'string.length', message: 'The {field} must be between {min} and {max} characters.');
    }

    /**
     * @return array{?int, ?int}
     */
    private function bounds(ValidationContext $context): array
    {
        $min = $context->hasParameter('min') ? $context->intParameter('min') : null;
        $max = $context->hasParameter('max') ? $context->intParameter('max') : null;

        if ($min === null && $max === null) {
            throw InvalidRuleException::missingBounds($context->field);
        }

        return [$min, $max];
    }
}
