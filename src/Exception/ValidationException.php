<?php

declare(strict_types=1);

namespace KaririCode\Validator\Exception;

use KaririCode\Exception\ValidationException as ValidationBase;
use KaririCode\Validator\Result\ValidationResult;

// ── Exception: ValidationException ───────────────────────────────────
// Input broke rules where the caller asked for an exception (code 3230).
// ─────────────────────────────────────────────────────────────────────

/**
 * Carries a ValidationResult with at least one error out of a caller that
 * cannot return it: the ValidationProcessor in a processing pipeline. The
 * message names how many errors there are and on which fields, never the
 * values; the result holds the messages.
 *
 * Preconditions: built only through the named constructor, from an
 * invalid result.
 *
 * Thread-safety: an exception, created per failure.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     try {
 *         $pipeline->process($input);
 *     } catch (ValidationException $e) {
 *         $errors = $e->result->messages();
 *     }
 *
 * References: ADR-002; ADR-004.
 */
final class ValidationException extends ValidationBase implements ValidatorException
{
    private function __construct(
        public readonly ValidationResult $result,
        string $message,
    ) {
        parent::__construct(ValidatorCode::ValidationFailed, $message);
    }

    public static function failed(ValidationResult $result): self
    {
        $fields = implode(', ', array_map(self::quote(...), $result->failedFields()));

        return new self($result, "{$result->errorCount()} validation error(s) on {$fields}.");
    }
}
