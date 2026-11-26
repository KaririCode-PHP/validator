<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Type;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Type\TypeRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypeRule::class)]
final class TypeRuleTest extends TestCase
{
    private TypeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new TypeRule();
    }

    #[DataProvider('typeCheckProvider')]
    public function testTypeChecks(string $type, mixed $value, bool $expected): void
    {
        $ctx = ValidationContextImpl::create([])
            ->withField('f')
            ->withParameters(['type' => $type]);

        $this->assertSame($expected, $this->rule->validate($value, $ctx));
    }

    public static function typeCheckProvider(): iterable
    {
        yield 'string valid' => ['string', 'hello', true];
        yield 'string invalid' => ['string', 42, false];
        yield 'int valid' => ['int', 42, true];
        yield 'int invalid' => ['int', '42', false];
        yield 'integer alias' => ['integer', 42, true];
        yield 'float valid' => ['float', 3.14, true];
        yield 'float invalid' => ['float', 42, false];
        yield 'double alias' => ['double', 3.14, true];
        yield 'bool valid' => ['bool', true, true];
        yield 'bool invalid' => ['bool', 1, false];
        yield 'boolean alias' => ['boolean', false, true];
        yield 'array valid' => ['array', [1, 2], true];
        yield 'array invalid' => ['array', 'hello', false];
        yield 'object valid' => ['object', new \stdClass(), true];
        yield 'object invalid' => ['object', 'hello', false];
        yield 'null valid' => ['null', null, true];
        yield 'null invalid' => ['null', '', false];
        yield 'numeric int' => ['numeric', 42, true];
        yield 'numeric float' => ['numeric', 3.14, true];
        yield 'numeric string' => ['numeric', '42', true];
        yield 'numeric invalid' => ['numeric', 'abc', false];
        yield 'scalar string' => ['scalar', 'hello', true];
        yield 'scalar array' => ['scalar', [], false];
        yield 'callable valid' => ['callable', 'strlen', true];
        yield 'callable invalid' => ['callable', 'not_a_function_xyz', false];
        yield 'unknown type' => ['unknown_type', 'hello', false];
    }

    public function testDefaultTypeIsString(): void
    {
        $ctx = ValidationContextImpl::create([])->withField('f');
        $this->assertTrue($this->rule->validate('hello', $ctx));
        $this->assertFalse($this->rule->validate(42, $ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('type.mismatch', $this->rule->getErrorCode());
    }

    public function testDefaultMessage(): void
    {
        $this->assertSame('The {field} must be of type {type}.', $this->rule->getDefaultMessage());
    }
}
