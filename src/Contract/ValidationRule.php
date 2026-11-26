<?php

declare(strict_types=1);

namespace KaririCode\Validator\Contract;

/**
 * Contract for individual validation rules.
 *
 * Each rule encapsulates a single validation concern following the
 * Specification Pattern (Evans, Domain-Driven Design, Ch. 9). Rules
 * are composable, reusable, and stateless — the validation logic
 * depends solely on the input value and optional configuration.
 *
 * ## ARFA 1.3 Alignment
 *
 * P1 (Immutable State): Rules are stateless; results are new objects.
 * P4 (Protocol Agnostic): Rules validate data regardless of source.
 *
 * ## Composition
 *
 * Rules compose via CompositeRule (AND) or pipeline chaining:
 *
 * ```php
 * $pipeline = new ValidationPipeline([
 *     new RequiredRule(),
 *     new EmailRule(),
 *     new LengthRule(max: 255),
 * ]);
 *
 * $result = $pipeline->validate('user@example.com', 'email');
 * ```
 *
 * ## Custom Rules
 *
 * ```php
 * final readonly class CnpjRule implements ValidationRule
 * {
 *     public function validate(mixed $value, ValidationContext $context): bool
 *     {
 *         if (!is_string($value)) return false;
 *         $digits = preg_replace('/\D/', '', $value);
 *         return strlen($digits) === 14 && $this->checkDigits($digits);
 *     }
 *
 *     public function getErrorCode(): string { return 'cnpj.invalid'; }
 *     public function getDefaultMessage(): string { return 'CNPJ inválido.'; }
 * }
 * ```
 *
 * @see CompositeRule For AND-composition of rules
 * @see ValidationPipeline For sequential rule execution
 *
 * References:
 * - Evans, E. "Domain-Driven Design", Addison-Wesley, 2003 (Specification Pattern)
 * - Fowler, M. "Specification", martinfowler.com/apsupp/spec.pdf
 *
 * @package KaririCode\Validator\Contract
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
interface ValidationRule
{
    /**
     * Validates the given value within the provided context.
     *
     * Returns true if the value satisfies this rule, false otherwise.
     * Must not throw exceptions for invalid input — return false instead.
     * Exceptions are reserved for misconfigured rules (programmer errors).
     *
     * @param mixed             $value   Value to validate (any type accepted)
     * @param ValidationContext $context Execution context with metadata
     *
     * @return bool True if valid, false otherwise
     */
    public function validate(mixed $value, ValidationContext $context): bool;

    /**
     * Machine-readable error code following dot-notation.
     *
     * Convention: `{domain}.{rule}` — e.g. `string.email`, `numeric.range`,
     * `logic.required`. Used for i18n lookups and programmatic error handling.
     */
    public function getErrorCode(): string;

    /**
     * Default human-readable error message.
     *
     * Used when no custom message is provided via configuration or
     * MessageResolver. Supports placeholder interpolation:
     * `{min}`, `{max}`, `{value}`, `{field}`.
     */
    public function getDefaultMessage(): string;
}
