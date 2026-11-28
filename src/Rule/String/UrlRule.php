<?php

declare(strict_types=1);

namespace KaririCode\Validator\Rule\String;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\Violation;

// ── Rule: UrlRule ────────────────────────────────────────────────────
// An absolute URL with an allowed scheme.
// ─────────────────────────────────────────────────────────────────────

/**
 * Accepts what FILTER_VALIDATE_URL accepts and whose scheme is in
 * 'schemes' (http and https unless set). The scheme check is what keeps
 * javascript:, data: and file: URLs out of links built from input.
 *
 * Preconditions: 'schemes', when given, is a list of lower-case scheme names.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     ['website' => ['url'], 'feed' => [['url', ['schemes' => ['https']]]]]
 *
 * References: RFC 3986 §3.1; OWASP ASVS 5.0 V1.2 (URL encoding and allow-listed schemes); SPEC-002 §2.
 */
final readonly class UrlRule implements ValidationRule
{
    #[\Override]
    public function validate(mixed $value, ValidationContext $context): bool
    {
        $schemes = $this->schemes($context);

        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return in_array(needle: strtolower((string) parse_url($value, PHP_URL_SCHEME)), haystack: $schemes, strict: true);
    }

    #[\Override]
    public function violation(ValidationContext $context): Violation
    {
        return new Violation(code: 'string.url', message: 'The {field} must be a valid URL.');
    }

    /**
     * @return list<string>
     */
    private function schemes(ValidationContext $context): array
    {
        if (!$context->hasParameter('schemes')) {
            return ['http', 'https'];
        }

        $schemes = $context->arrayParameter('schemes');

        if ($schemes === [] || !array_is_list($schemes) || !array_all($schemes, static fn(mixed $scheme): bool => is_string($scheme))) {
            throw InvalidRuleException::invalidParameter(field: $context->field, parameter: 'schemes', expected: 'a non-empty list of scheme names', given: get_debug_type($schemes));
        }

        /** @var list<string> $schemes */
        return array_map(strtolower(...), $schemes);
    }
}
