<?php

declare(strict_types=1);

namespace KaririCode\Validator\Event;

/**
 * Emitted when a validation operation begins.
 *
 * ARFA 1.3 P5 (Continuous Observability): enables tracing and metrics.
 *
 * @package KaririCode\Validator\Event
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ValidationStartedEvent
{
    /**
     * @param array<string> $fields    Fields being validated
     * @param string|null   $group     Active validation group
     * @param float         $timestamp Microtime when validation started
     */
    public function __construct(
        public array $fields,
        public ?string $group = null,
        public float $timestamp = 0.0,
    ) {
    }
}
