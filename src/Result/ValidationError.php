<?php

declare(strict_types=1);

namespace KaririCode\Validator\Result;

/**
 * Immutable value object representing a single validation error.
 *
 * Carries the field name, machine-readable error code, human-readable
 * message, and optional context for structured error reporting.
 *
 * ARFA 1.3 P1: Readonly — errors are immutable once created.
 *
 * References:
 * - RFC 7807 (Problem Details for HTTP APIs) — error structure inspiration
 * - Evans, E. "Domain-Driven Design" — Value Object pattern
 *
 * @package KaririCode\Validator\Result
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ValidationError
{
    /**
     * @param string               $field     Field name (dot-notation for nested)
     * @param string               $errorCode Machine-readable code (e.g. 'string.email')
     * @param string               $message   Human-readable error message
     * @param array<string, mixed> $context   Additional metadata (value, constraints)
     */
    public function __construct(
        public string $field,
        public string $errorCode,
        public string $message,
        public array $context = [],
    ) {
    }
}
