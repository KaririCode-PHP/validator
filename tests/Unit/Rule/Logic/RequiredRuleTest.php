<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Logic;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequiredRuleTest extends TestCase
{
    private RequiredRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RequiredRule();
    }

    #[DataProvider('validDataProvider')]
    public function testValidValues(mixed $value): void
    {
        $ctx = ValidationContextImpl::create([])->withField('field');
        $this->assertTrue($this->rule->validate($value, $ctx));
    }

    public static function validDataProvider(): iterable
    {
        yield 'non-empty string' => ['hello'];
        yield 'zero string' => ['0'];
        yield 'integer zero' => [0];
        yield 'float zero' => [0.0];
        yield 'false' => [false];
        yield 'non-empty array' => [['a']];
        yield 'true' => [true];
        yield 'integer' => [42];
        yield 'object' => [new \stdClass()];
    }

    #[DataProvider('invalidDataProvider')]
    public function testInvalidValues(mixed $value): void
    {
        $ctx = ValidationContextImpl::create([])->withField('field');
        $this->assertFalse($this->rule->validate($value, $ctx));
    }

    public static function invalidDataProvider(): iterable
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
        yield 'whitespace only' => ['   '];
        yield 'tab and newline' => ["\t\n"];
        yield 'empty array' => [[]];
    }

    public function testErrorCode(): void
    {
        $this->assertSame('logic.required', $this->rule->getErrorCode());
    }

    public function testDefaultMessage(): void
    {
        $this->assertStringContainsString('{field}', $this->rule->getDefaultMessage());
    }
}
