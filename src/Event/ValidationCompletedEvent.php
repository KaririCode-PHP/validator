<?php

declare(strict_types=1);

namespace KaririCode\Validator\Event;

use KaririCode\Validator\Result\ValidationResult;

/**
 * Emitted when a validation operation completes (success or failure).
 *
 * @package KaririCode\Validator\Event
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ValidationCompletedEvent
{
    public function __construct(
        public ValidationResult $result,
        public float $durationMs,
    ) {
    }
}
