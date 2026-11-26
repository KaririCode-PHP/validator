<?php

declare(strict_types=1);

namespace KaririCode\Validator\Exception;

use KaririCode\Validator\Result\ValidationResult;

/**
 * Thrown when validation fails and the caller requires exception-based flow.
 *
 * Carries the full ValidationResult for structured error reporting.
 * Prefer returning ValidationResult directly; use this exception only
 * in contexts where exception propagation is the established pattern
 * (e.g. middleware pipelines, form handlers).
 *
 * @package KaririCode\Validator\Exception
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class ValidationException extends \RuntimeException
{
    public function __construct(
        public readonly ValidationResult $result,
        string $message = 'Validation failed.',
        int $code = 422,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromResult(ValidationResult $result): self
    {
        $count = $result->errorCount();
        $fields = implode(', ', $result->failedFields());

        return new self(
            result: $result,
            message: "{$count} validation error(s) on field(s): {$fields}",
        );
    }
}
