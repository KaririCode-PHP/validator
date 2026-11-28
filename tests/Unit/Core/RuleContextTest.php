<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use Closure;
use KaririCode\Validator\Core\DataPath;
use KaririCode\Validator\Core\NumericValue;
use KaririCode\Validator\Core\RuleContext;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Exception\ValidatorCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

// ── Unit: RuleContextTest ────────────────────────────────────────────
// The context: copies, typed parameters, field paths.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(RuleContext::class)]
#[CoversClass(DataPath::class)]
#[CoversClass(NumericValue::class)]
final class RuleContextTest extends TestCase
{
    #[Test]
    public function copiesKeepWhatTheyDoNotChange(): void
    {
        $context = new RuleContext(data: ['a' => 1], field: 'a', parameters: ['min' => 1], group: 'create');
        $field = $context->withField('b');
        $parameters = $context->withParameters(['max' => 2, 'min' => 0]);

        self::assertSame([['a' => 1], 'b', ['min' => 1], 'create'], [$field->data, $field->field, $field->parameters, $field->group]);
        self::assertSame([['a' => 1], 'a', ['min' => 0, 'max' => 2], 'create'], [$parameters->data, $parameters->field, $parameters->parameters, $parameters->group]);
        self::assertSame(['min' => 1], $context->parameters);
        self::assertSame(['', [], [], null], [(new RuleContext())->field, (new RuleContext())->data, (new RuleContext())->parameters, (new RuleContext())->group]);
    }

    #[Test]
    public function parametersAreReadWithTheirTypesChecked(): void
    {
        $context = new RuleContext(field: 'f', parameters: ['int' => 3, 'float' => 1.5, 'text' => 'x', 'list' => [1], 'null' => null]);

        self::assertTrue($context->hasParameter('null'));
        self::assertFalse($context->hasParameter('missing'));
        self::assertNull($context->parameter('null', 'default'));
        self::assertSame('default', $context->parameter('missing', 'default'));
        self::assertSame(3, $context->intParameter('int'));
        self::assertSame(7, $context->intParameter('missing', 7));
        self::assertSame(3, $context->numberParameter('int'));
        self::assertSame(1.5, $context->numberParameter('float'));
        self::assertSame(2.5, $context->numberParameter('missing', 2.5));
        self::assertSame('x', $context->stringParameter('text'));
        self::assertSame('y', $context->stringParameter('missing', 'y'));
        self::assertSame([1], $context->arrayParameter('list'));
    }

    /**
     * @return iterable<string, array{Closure(RuleContext): mixed, ValidatorCode, non-empty-string}>
     */
    public static function refusals(): iterable
    {
        yield 'int missing' => [static fn(RuleContext $c): mixed => $c->intParameter('missing'), ValidatorCode::MissingRuleParameter, 'needs the parameter "missing".'];
        yield 'int from a float' => [static fn(RuleContext $c): mixed => $c->intParameter('float'), ValidatorCode::InvalidRuleParameter, 'must be an integer, float given.'];
        yield 'int from null' => [static fn(RuleContext $c): mixed => $c->intParameter('null'), ValidatorCode::InvalidRuleParameter, 'must be an integer, null given.'];
        yield 'number from text' => [static fn(RuleContext $c): mixed => $c->numberParameter('text'), ValidatorCode::InvalidRuleParameter, 'must be a finite number, string given.'];
        yield 'number from NAN' => [static fn(RuleContext $c): mixed => $c->numberParameter('nan'), ValidatorCode::InvalidRuleParameter, 'must be a finite number, float given.'];
        yield 'number missing' => [static fn(RuleContext $c): mixed => $c->numberParameter('missing'), ValidatorCode::MissingRuleParameter, 'needs the parameter "missing".'];
        yield 'string from an int' => [static fn(RuleContext $c): mixed => $c->stringParameter('int'), ValidatorCode::InvalidRuleParameter, 'must be a string, int given.'];
        yield 'string missing' => [static fn(RuleContext $c): mixed => $c->stringParameter('missing'), ValidatorCode::MissingRuleParameter, 'needs the parameter "missing".'];
        yield 'array from text' => [static fn(RuleContext $c): mixed => $c->arrayParameter('text'), ValidatorCode::InvalidRuleParameter, 'must be an array, string given.'];
        yield 'array missing' => [static fn(RuleContext $c): mixed => $c->arrayParameter('missing'), ValidatorCode::MissingRuleParameter, 'needs the parameter "missing".'];
    }

    /**
     * @param Closure(RuleContext): mixed $read
     * @param non-empty-string            $ending
     */
    #[Test]
    #[DataProvider('refusals')]
    public function wrongParametersAreTheApplicationsMistake(Closure $read, ValidatorCode $code, string $ending): void
    {
        $context = new RuleContext(field: 'age', parameters: ['int' => 3, 'float' => 1.5, 'text' => 'x', 'null' => null, 'nan' => NAN]);

        try {
            $read($context);
            self::fail('The parameter was accepted.');
        } catch (InvalidRuleException $exception) {
            self::assertSame($code, $exception->errorCode);
            self::assertStringContainsString('field "age"', $exception->getMessage());
            self::assertStringEndsWith($ending, $exception->getMessage());
        }
    }

    #[Test]
    public function fieldsAreFoundByDottedPathsLiteralKeysFirst(): void
    {
        $context = new RuleContext(data: ['user' => ['name' => 'Ana', 'nick' => null], 'items' => ['x', 'y'], 'a.b' => 'literal', 'a' => ['b' => 'nested']]);

        self::assertSame('Ana', $context->fieldValue('user.name'));
        self::assertSame('y', $context->fieldValue('items.1'));
        self::assertSame('literal', $context->fieldValue('a.b'));
        self::assertTrue($context->hasField('user.nick'));
        self::assertNull($context->fieldValue('user.nick'));
        self::assertFalse($context->hasField('user.age'));
        self::assertNull($context->fieldValue('user.age'));
        self::assertFalse($context->hasField('user.name.first'));
        self::assertFalse($context->hasField('missing'));
        self::assertTrue(DataPath::exists(['' => 1], ''));
    }

    /**
     * @return iterable<string, array{mixed, int|float|null}>
     */
    public static function numbers(): iterable
    {
        yield 'int' => [42, 42];
        yield 'negative int string' => ['-7', -7];
        yield 'padded int string' => [' 12 ', 12];
        yield 'float' => [1.5, 1.5];
        yield 'float string' => ['1.5', 1.5];
        yield 'exponent string' => ['1e3', 1000.0];
        yield 'leading zero string' => ['012', 12.0];
        yield 'int beyond PHP_INT_MAX' => ['9223372036854775808', 9.2233720368547758E18];
        yield 'infinity' => [INF, null];
        yield 'NAN' => [NAN, null];
        yield 'true' => [true, null];
        yield 'text' => ['12abc', null];
        yield 'null' => [null, null];
    }

    #[Test]
    #[DataProvider('numbers')]
    public function numericValuesKeepIntegersAsIntegers(mixed $value, int|float|null $expected): void
    {
        self::assertSame($expected, NumericValue::of($value));
    }
}
