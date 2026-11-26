<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use KaririCode\Validator\Core\ValidationContextImpl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationContextImpl::class)]
final class ValidationContextImplTest extends TestCase
{
    public function testCreateFactoryMethod(): void
    {
        $ctx = ValidationContextImpl::create(['name' => 'Walmir'], 'update');

        $this->assertSame('', $ctx->getFieldName());
        $this->assertSame(['name' => 'Walmir'], $ctx->getRootData());
        $this->assertSame([], $ctx->getParameters());
        $this->assertSame('update', $ctx->getGroup());
    }

    public function testCreateWithDefaultGroup(): void
    {
        $ctx = ValidationContextImpl::create([]);
        $this->assertNull($ctx->getGroup());
    }

    public function testWithFieldCreatesNewInstance(): void
    {
        $original = ValidationContextImpl::create(['email' => 'a@b.com']);
        $derived = $original->withField('email');

        $this->assertSame('', $original->getFieldName());
        $this->assertSame('email', $derived->getFieldName());
        $this->assertSame($original->getRootData(), $derived->getRootData());
    }

    public function testWithParametersCreatesNewInstance(): void
    {
        $original = ValidationContextImpl::create([])->withField('name');
        $derived = $original->withParameters(['min' => 3, 'max' => 50]);

        $this->assertSame([], $original->getParameters());
        $this->assertSame(['min' => 3, 'max' => 50], $derived->getParameters());
        $this->assertSame('name', $derived->getFieldName());
    }

    public function testGetParameter(): void
    {
        $ctx = (new ValidationContextImpl('f', [], ['min' => 5]));

        $this->assertSame(5, $ctx->getParameter('min'));
        $this->assertNull($ctx->getParameter('max'));
        $this->assertSame(100, $ctx->getParameter('max', 100));
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(ValidationContextImpl::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
