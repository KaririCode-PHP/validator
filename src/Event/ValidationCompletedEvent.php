<?php

declare(strict_types=1);

namespace KaririCode\Validator\Event;

use KaririCode\Validator\Result\ValidationResult;

// ── Event: ValidationCompletedEvent ──────────────────────────────────
// Reports a finished validation and how long it took.
// ─────────────────────────────────────────────────────────────────────

/**
 * Gives the observer the result of a validation and its duration in
 * milliseconds, measured with the monotonic clock. The result holds
 * messages, not values.
 *
 * Preconditions: none.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $observer = static function (object $event) use ($metrics): void {
 *         if ($event instanceof ValidationCompletedEvent) {
 *             $metrics->observe('validation_ms', $event->durationMs);
 *         }
 *     };
 *
 * References: ADR-006; ARFA 1.3 P5.
 */
final readonly class ValidationCompletedEvent
{
    public function __construct(
        public ValidationResult $result,
        public float $durationMs,
    ) {}
}
