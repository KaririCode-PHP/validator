<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Numeric;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Numeric\PositiveRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PositiveRuleTest extends TestCase
{
    private PositiveRule $rule;
    private ValidationContextImpl $ctx;

    protected function setUp(): void
    {
        $this->rule = new PositiveRule();
        $this->ctx = ValidationContextImpl::create([])->withField('f');
    }

    #[DataProvider('validProvider')]
    public function testPositiveValues(mixed $value): void
    {
        $this->assertTrue($this->rule->validate($value, $this->ctx));
    }

    public static function validProvider(): iterable
    {
        yield 'integer' => [1];
        yield 'float' => [0.001];
        yield 'large' => [PHP_INT_MAX];
        yield 'string numeric' => ['42'];
    }

    #[DataProvider('invalidProvider')]
    public function testNonPositiveValues(mixed $value): void
    {
        $this->assertFalse($this->rule->validate($value, $this->ctx));
    }

    public static function invalidProvider(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
        yield 'negative float' => [-0.5];
        yield 'null' => [null];
        yield 'string' => ['abc'];
        yield 'empty string' => [''];
        yield 'float zero' => [0.0];
    }

    public function testErrorCode(): void
    {
        $this->assertSame('numeric.positive', $this->rule->getErrorCode());
    }
}
