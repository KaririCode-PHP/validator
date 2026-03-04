<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Event;

use KaririCode\Validator\Event\ValidationStartedEvent;
use PHPUnit\Framework\TestCase;

final class ValidationStartedEventTest extends TestCase
{
    public function testProperties(): void
    {
        $event = new ValidationStartedEvent(
            fields: ['email', 'name'],
            group: 'create',
            timestamp: 1234567.89,
        );

        $this->assertSame(['email', 'name'], $event->fields);
        $this->assertSame('create', $event->group);
        $this->assertSame(1234567.89, $event->timestamp);
    }

    public function testDefaultValues(): void
    {
        $event = new ValidationStartedEvent(fields: ['email']);

        $this->assertNull($event->group);
        $this->assertSame(0.0, $event->timestamp);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(ValidationStartedEvent::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
