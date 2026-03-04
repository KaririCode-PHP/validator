<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Attribute;

use KaririCode\Validator\Attribute\ValidationGroup;
use PHPUnit\Framework\TestCase;

final class ValidationGroupTest extends TestCase
{
    public function testGroups(): void
    {
        $group = new ValidationGroup('create', 'update');

        $this->assertSame(['create', 'update'], $group->groups);
    }

    public function testSingleGroup(): void
    {
        $group = new ValidationGroup('create');

        $this->assertSame(['create'], $group->groups);
    }

    public function testEmptyGroups(): void
    {
        $group = new ValidationGroup();
        $this->assertSame([], $group->groups);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(ValidationGroup::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
