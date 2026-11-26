<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Attribute;

use KaririCode\Validator\Attribute\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validate::class)]
final class ValidateTest extends TestCase
{
    public function testStringRules(): void
    {
        $validate = new Validate('required', 'email');

        $this->assertSame(['required', 'email'], $validate->rules);
    }

    public function testArrayRules(): void
    {
        $validate = new Validate(
            'required',
            ['length', ['min' => 3, 'max' => 255]],
        );

        $this->assertCount(2, $validate->rules);
        $this->assertSame('required', $validate->rules[0]);
        $this->assertSame(['length', ['min' => 3, 'max' => 255]], $validate->rules[1]);
    }

    public function testEmptyRules(): void
    {
        $validate = new Validate();
        $this->assertSame([], $validate->rules);
    }

    public function testIsTargetProperty(): void
    {
        $reflection = new \ReflectionClass(Validate::class);
        $attributes = $reflection->getAttributes(\Attribute::class);
        $this->assertCount(1, $attributes);

        $attr = $attributes[0]->newInstance();
        $this->assertSame(
            \Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE,
            $attr->flags,
        );
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(Validate::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
