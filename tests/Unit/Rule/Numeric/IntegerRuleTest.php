<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Numeric;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Numeric\IntegerRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IntegerRuleTest extends TestCase
{
    private IntegerRule $rule;
    private ValidationContextImpl $ctx;

    protected function setUp(): void
    {
        $this->rule = new IntegerRule();
        $this->ctx = ValidationContextImpl::create([])->withField('f');
    }

    #[DataProvider('validProvider')]
    public function testValidIntegers(mixed $value): void
    {
        $this->assertTrue($this->rule->validate($value, $this->ctx));
    }

    public static function validProvider(): iterable
    {
        yield 'positive' => [42];
        yield 'zero' => [0];
        yield 'negative' => [-5];
        yield 'string int' => ['100'];
        yield 'string negative' => ['-7'];
        yield 'string zero' => ['0'];
    }

    #[DataProvider('invalidProvider')]
    public function testInvalidIntegers(mixed $value): void
    {
        $this->assertFalse($this->rule->validate($value, $this->ctx));
    }

    public static function invalidProvider(): iterable
    {
        yield 'float' => [3.14];
        yield 'string float' => ['3.14'];
        yield 'alpha' => ['abc'];
        yield 'null' => [null];
        yield 'empty' => [''];
        yield 'array' => [[1]];
    }

    /**
     * filter_var(true, FILTER_VALIDATE_INT) returns 1 in PHP 8.x
     * (boolean coerced to string "1" which passes FILTER_VALIDATE_INT).
     * This is a known PHP behavior, not a bug in our rule.
     */
    public function testBooleanTrueCoercesToValidInt(): void
    {
        $this->assertTrue($this->rule->validate(true, $this->ctx));
    }

    public function testBooleanFalseIsNotAValidInt(): void
    {
        // filter_var(false) coerces to "" (empty string) which fails FILTER_VALIDATE_INT
        $this->assertFalse($this->rule->validate(false, $this->ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('numeric.integer', $this->rule->getErrorCode());
    }
}
