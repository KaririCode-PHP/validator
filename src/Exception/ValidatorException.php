<?php

declare(strict_types=1);

namespace KaririCode\Validator\Exception;

use KaririCode\Exception\ErrorCode;
use Throwable;

// ── Exception: ValidatorException ────────────────────────────────────
// Marks every exception kariricode/validator throws.
// ─────────────────────────────────────────────────────────────────────

/**
 * Lets a caller catch whatever this package throws with one type, while
 * each concrete exception also belongs to one kariricode/contract family
 * (Configuration or Validation) through its kariricode/exception base.
 *
 * Preconditions: none.
 *
 * Thread-safety: implementations are exceptions, created per failure.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     try {
 *         $processor->process($input);
 *     } catch (ValidatorException $e) {
 *         $code = $e->errorCode; // a ValidatorCode case
 *     }
 *
 * References: ADR-004.
 */
interface ValidatorException extends Throwable
{
    public ErrorCode $errorCode { get; }
}
