<?php

declare(strict_types=1);

namespace KaririCode\Validator\Event;

// ── Event: ValidationStartedEvent ────────────────────────────────────
// Announces a validation before its first rule runs.
// ─────────────────────────────────────────────────────────────────────

/**
 * Tells the observer which fields are about to be validated, in which
 * group, and when (Unix time in seconds, with microseconds). It carries
 * field names only, never values.
 *
 * Preconditions: none.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $engine = new ValidatorEngine(observer: static fn (object $event) => $dispatcher->dispatch($event));
 *
 * References: ADR-006; ARFA 1.3 P5.
 */
final readonly class ValidationStartedEvent
{
    /**
     * @param list<string> $fields
     */
    public function __construct(
        public array $fields,
        public ?string $group,
        public float $timestamp,
    ) {}
}
