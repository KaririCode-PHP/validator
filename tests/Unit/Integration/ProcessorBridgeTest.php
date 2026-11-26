<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Integration;

use KaririCode\Validator\Core\InMemoryRuleRegistry;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Exception\ValidationException;
use KaririCode\Validator\Integration\ProcessorBridge;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use KaririCode\Validator\Rule\String\EmailRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessorBridge::class)]
final class ProcessorBridgeTest extends TestCase
{
    private ValidatorEngine $engine;

    protected function setUp(): void
    {
        $registry = new InMemoryRuleRegistry();
        $registry->register('required', new RequiredRule());
        $registry->register('email', new EmailRule());

        $this->engine = new ValidatorEngine($registry);
    }

    public function testValidDataReturnsDataAndResult(): void
    {
        $bridge = new ProcessorBridge(
            $this->engine,
            ['email' => ['required', 'email']],
        );

        $output = $bridge->process(['email' => 'user@example.com']);

        $this->assertSame(['email' => 'user@example.com'], $output['data']);
        $this->assertTrue($output['result']->isValid());
    }

    public function testInvalidDataThrowsWhenThrowOnFailure(): void
    {
        $bridge = new ProcessorBridge(
            $this->engine,
            ['email' => ['required', 'email']],
            throwOnFailure: true,
        );

        $this->expectException(ValidationException::class);

        $bridge->process(['email' => 'bad']);
    }

    public function testInvalidDataReturnsResultWhenNotThrow(): void
    {
        $bridge = new ProcessorBridge(
            $this->engine,
            ['email' => ['required', 'email']],
            throwOnFailure: false,
        );

        $output = $bridge->process(['email' => 'bad']);

        $this->assertFalse($output['result']->isValid());
        $this->assertSame(['email' => 'bad'], $output['data']);
    }

    public function testDataIsNotMutated(): void
    {
        $bridge = new ProcessorBridge(
            $this->engine,
            ['email' => ['required', 'email']],
            throwOnFailure: false,
        );

        $data = ['email' => 'user@example.com', 'extra' => 'preserved'];
        $output = $bridge->process($data);

        $this->assertSame($data, $output['data']);
    }
}
