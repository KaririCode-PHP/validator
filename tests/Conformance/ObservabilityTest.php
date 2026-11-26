<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Conformance;

use KaririCode\Validator\Event\ValidationCompletedEvent;
use KaririCode\Validator\Event\ValidationStartedEvent;
use KaririCode\Validator\Result\ValidationResult;
use PHPUnit\Framework\TestCase;

/**
 * ARFA 1.3 P5 — Continuous Observability.
 *
 * ∀operation ∈ System: ∃trace(operation) ∧ ∃metrics(operation)
 */
final class ObservabilityTest extends TestCase
{
    public function testValidationStartedEventCarriesTraceData(): void
    {
        $event = new ValidationStartedEvent(
            fields: ['email', 'name', 'age'],
            group: 'create',
            timestamp: microtime(true),
        );

        $this->assertCount(3, $event->fields);
        $this->assertSame('create', $event->group);
        $this->assertGreaterThan(0, $event->timestamp);
    }

    public function testValidationCompletedEventCarriesMetrics(): void
    {
        $result = new ValidationResult();
        $result->add('email', 'string.email', 'Invalid.');

        $event = new ValidationCompletedEvent(
            result: $result,
            durationMs: 1.234,
        );

        $this->assertSame(1, $event->result->errorCount());
        $this->assertSame(1.234, $event->durationMs);
    }

    public function testDurationMeasurementFeasibility(): void
    {
        $start = hrtime(true);

        // Simulated validation work — 1ms
        usleep(1000);

        $durationMs = (hrtime(true) - $start) / 1_000_000;

        $this->assertGreaterThan(0.5, $durationMs);
    }
}
