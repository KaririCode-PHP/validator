<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\Violation;

// ── Rule: SlugRule ───────────────────────────────────────────────────
// A lower-case URL slug.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts lower-case ASCII letters and digits in groups joined by single
 * hyphens: "my-post-2" passes; "My-Post", "-a", "a--b" and "a_b" fail.
 *
 * Preconditions: none; non-strings fail.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['slug' => ['slug']]
 *
 * References: SPEC-002 §2.
 */
final readonly class SlugRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $value) === 1;
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.slug', message: 'The {field} must be a valid URL slug.');
    }
}
