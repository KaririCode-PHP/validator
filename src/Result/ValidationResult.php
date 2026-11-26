<?php

declare(strict_types=1);

namespace KaririCode\Validator\Result;

/**
 * Aggregates validation errors for a complete validation operation.
 *
 * Collects ALL errors (not just the first), enabling UX patterns that
 * show every problem at once. Errors are grouped by field for easy
 * access in form rendering and API responses.
 *
 * ## ARFA 1.3 Alignment
 *
 * P1 (Immutable State): Results are append-only; errors cannot be removed.
 * P2 (Reactive Composition): Results merge via `merge()` for pipeline composition.
 *
 * ## Error Collection Strategy (ADR-001)
 *
 * Collects all errors instead of fail-fast because:
 * 1. Better UX — users see all problems at once
 * 2. Enables cross-field validation without order dependency
 * 3. Composable — merge results from sub-validators
 *
 * References:
 * - Martin Fowler, "Notification Pattern", martinfowler.com (2004)
 *
 * @package KaririCode\Validator\Result
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class ValidationResult
{
    /** @var array<int, ValidationError> */
    private array $errors = [];

    /**
     * Adds an error to the result.
     */
    public function addError(ValidationError $error): self
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Creates and adds an error in one call.
     *
     * @param array<string, mixed> $context
     */
    public function add(
        string $field,
        string $errorCode,
        string $message,
        array $context = [],
    ): self {
        $this->errors[] = new ValidationError($field, $errorCode, $message, $context);

        return $this;
    }

    /**
     * Merges another result's errors into this one.
     *
     * Enables pipeline composition: each step validates independently,
     * then results are merged for a unified error report.
     */
    public function merge(self $other): self
    {
        foreach ($other->errors as $error) {
            $this->errors[] = $error;
        }

        return $this;
    }

    /**
     * Whether the validated data passed all rules.
     */
    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /**
     * All collected errors.
     *
     * @return array<int, ValidationError>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Errors for a specific field.
     *
     * @return array<int, ValidationError>
     */
    public function errorsFor(string $field): array
    {
        return array_values(
            array_filter(
                $this->errors,
                static fn (ValidationError $error): bool => $error->field === $field,
            ),
        );
    }

    /**
     * First error for a specific field, or null.
     */
    public function firstErrorFor(string $field): ?ValidationError
    {
        $errors = $this->errorsFor($field);

        return $errors !== [] ? $errors[0] : null;
    }

    /**
     * All fields that have errors.
     *
     * @return array<string>
     */
    public function failedFields(): array
    {
        return array_values(
            array_unique(
                array_map(
                    static fn (ValidationError $error): string => $error->field,
                    $this->errors,
                ),
            ),
        );
    }

    /**
     * Total error count.
     */
    public function errorCount(): int
    {
        return \count($this->errors);
    }
}
