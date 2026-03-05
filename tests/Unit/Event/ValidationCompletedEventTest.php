<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Event;

use KaririCode\Validator\Event\ValidationCompletedEvent;
use KaririCode\Validator\Result\ValidationResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationCompletedEvent::class)]
final class ValidationCompletedEventTest extends TestCase
{
    public function testProperties(): void
    {
        $result = new ValidationResult();
        $event = new ValidationCompletedEvent(result: $result, durationMs: 12.5);

        $this->assertSame($result, $event->result);
        $this->assertSame(12.5, $event->durationMs);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(ValidationCompletedEvent::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
