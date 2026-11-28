<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule;

use ArrayObject;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\DateValue;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Rule\Date\DateAfterRule;
use KaririCode\Validator\Rule\Date\DateBeforeRule;
use KaririCode\Validator\Rule\Date\DateBetweenRule;
use KaririCode\Validator\Rule\Date\DateFormatRule;
use KaririCode\Validator\Rule\Type\InstanceOfRule;
use KaririCode\Validator\Rule\Type\TypeRule;
use KaririCode\Validator\Tests\Support\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

// ── Unit: DateAndTypeRulesTest ───────────────────────────────────────
// The date rules, strict about formats, and the type rules.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(DateFormatRule::class)]
#[CoversClass(DateBeforeRule::class)]
#[CoversClass(DateAfterRule::class)]
#[CoversClass(DateBetweenRule::class)]
#[CoversClass(DateValue::class)]
#[CoversClass(TypeRule::class)]
#[CoversClass(InstanceOfRule::class)]
final class DateAndTypeRulesTest extends RuleTestCase
{
    /**
     * @return iterable<string, array{bool, ValidationRule, mixed, 3?: array<string, mixed>}>
     */
    public static function cases(): iterable
    {
        yield 'date_format: ISO date' => [true, new DateFormatRule(), '2026-02-28'];
        yield 'date_format: February 30' => [false, new DateFormatRule(), '2026-02-30'];
        yield 'date_format: unpadded' => [false, new DateFormatRule(), '2026-2-5'];
        yield 'date_format: trailing space' => [false, new DateFormatRule(), '2026-02-28 '];
        yield 'date_format: time in a date-only format' => [false, new DateFormatRule(), '2026-02-28T10:00'];
        yield 'date_format: own format' => [true, new DateFormatRule(), '28/02/2026', ['format' => 'd/m/Y']];
        yield 'date_format: own format with time' => [true, new DateFormatRule(), '2026-02-28 10:30', ['format' => 'Y-m-d H:i']];
        yield 'date_format: hour 25' => [false, new DateFormatRule(), '2026-02-28 25:30', ['format' => 'Y-m-d H:i']];
        yield 'date_format: integer' => [false, new DateFormatRule(), 20260228];

        $before = ['before' => '2024-01-01'];
        yield 'date_before: earlier' => [true, new DateBeforeRule(), '2023-12-31', $before];
        yield 'date_before: same day' => [false, new DateBeforeRule(), '2024-01-01', $before];
        yield 'date_before: later' => [false, new DateBeforeRule(), '2024-01-02', $before];
        yield 'date_before: a date object' => [true, new DateBeforeRule(), new DateTimeImmutable('2023-06-01'), $before];
        yield 'date_before: a bound object' => [true, new DateBeforeRule(), '2023-12-31', ['before' => new DateTimeImmutable('2024-01-01')]];
        yield 'date_before: own format' => [true, new DateBeforeRule(), '31/12/2023', ['before' => '01/01/2024', 'format' => 'd/m/Y']];
        yield 'date_before: not a date' => [false, new DateBeforeRule(), 'yesterday', $before];
        yield 'date_before: overflowing date' => [false, new DateBeforeRule(), '2023-02-30', $before];
        yield 'date_before: integer' => [false, new DateBeforeRule(), 20231231, $before];

        $after = ['after' => '2024-01-01'];
        yield 'date_after: later' => [true, new DateAfterRule(), '2024-01-02', $after];
        yield 'date_after: same day' => [false, new DateAfterRule(), '2024-01-01', $after];
        yield 'date_after: earlier' => [false, new DateAfterRule(), '2023-12-31', $after];

        $window = ['after' => '2026-12-01', 'before' => '2027-01-31'];
        yield 'date_between: inside' => [true, new DateBetweenRule(), '2026-12-15', $window];
        yield 'date_between: on after' => [false, new DateBetweenRule(), '2026-12-01', $window];
        yield 'date_between: on before' => [false, new DateBetweenRule(), '2027-01-31', $window];
        yield 'date_between: past before' => [false, new DateBetweenRule(), '2027-02-01', $window];
        yield 'date_between: not a date' => [false, new DateBetweenRule(), 'soon', $window];

        foreach ([
            'string' => ['x', 1], 'int' => [1, '1'], 'integer' => [1, 1.0], 'float' => [1.5, 1], 'double' => [1.5, '1.5'],
            'bool' => [true, 1], 'boolean' => [false, 0], 'array' => [[], 'x'], 'object' => [new stdClass(), []],
            'null' => [null, ''], 'numeric' => ['1.5', 'x'], 'scalar' => [1, []], 'callable' => ['strlen', 'no_such_function'],
        ] as $type => [$match, $mismatch]) {
            yield "type {$type}: match" => [true, new TypeRule(), $match, ['type' => $type]];
            yield "type {$type}: mismatch" => [false, new TypeRule(), $mismatch, ['type' => $type]];
        }

        yield 'instance_of: interface' => [true, new InstanceOfRule(), new DateTimeImmutable(), ['class' => DateTimeInterface::class]];
        yield 'instance_of: other class' => [false, new InstanceOfRule(), new stdClass(), ['class' => DateTimeInterface::class]];
        yield 'instance_of: a string' => [false, new InstanceOfRule(), 'DateTimeImmutable', ['class' => DateTimeInterface::class]];
        yield 'instance_of: an enum' => [true, new InstanceOfRule(), ValidatorCode::InvalidSetting, ['class' => ValidatorCode::class]];
        yield 'instance_of: a class' => [true, new InstanceOfRule(), new ArrayObject(), ['class' => ArrayObject::class]];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[Test]
    #[DataProvider('cases')]
    public function rulesAcceptAndRejectValues(bool $expected, ValidationRule $rule, mixed $value, array $parameters = []): void
    {
        self::assertRule($expected, $rule, $value, $parameters);
    }

    #[Test]
    public function datesCompareAsInstants(): void
    {
        $saoPaulo = new DateTimeImmutable('2024-01-01 00:30', new DateTimeZone('America/Sao_Paulo'));

        self::assertRule(true, new DateAfterRule(), $saoPaulo, ['after' => new DateTimeImmutable('2024-01-01 03:00', new DateTimeZone('UTC'))]);
        self::assertSame('2024-01-01 00:30', DateValue::of($saoPaulo, 'Y-m-d')?->format('Y-m-d H:i'));
        self::assertNull(DateValue::of(1704067200, 'U'));
        self::assertNull(DateValue::parse('2024-13-01', 'Y-m-d'));
        self::assertSame('2024-01-31 00:00:00', DateValue::parse('2024-01-31', 'Y-m-d')?->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function violationsNameTheirCodes(): void
    {
        self::assertViolation('date.format', new DateFormatRule());
        self::assertSame(['format' => 'Y-m-d'], (new DateFormatRule())->violation(self::context())->parameters);
        self::assertSame(['format' => 'd/m/Y'], (new DateFormatRule())->violation(self::context(['format' => 'd/m/Y']))->parameters);
        self::assertViolation('date.before', new DateBeforeRule());
        self::assertViolation('date.after', new DateAfterRule());
        self::assertViolation('date.between', new DateBetweenRule());
        self::assertViolation('type.mismatch', new TypeRule());
        self::assertViolation('type.instance_of', new InstanceOfRule());
    }

    /**
     * @return iterable<string, array{ValidatorCode, ValidationRule, array<string, mixed>}>
     */
    public static function misconfigurations(): iterable
    {
        yield 'date_before without before' => [ValidatorCode::MissingRuleParameter, new DateBeforeRule(), []];
        yield 'date_before with a word' => [ValidatorCode::InvalidRuleParameter, new DateBeforeRule(), ['before' => 'tomorrow']];
        yield 'date_after with a timestamp' => [ValidatorCode::InvalidRuleParameter, new DateAfterRule(), ['after' => 1704067200]];
        yield 'date_between without after' => [ValidatorCode::MissingRuleParameter, new DateBetweenRule(), ['before' => '2024-01-01']];
        yield 'date_between without before' => [ValidatorCode::MissingRuleParameter, new DateBetweenRule(), ['after' => '2024-01-01']];
        yield 'date_format with an integer format' => [ValidatorCode::InvalidRuleParameter, new DateFormatRule(), ['format' => 5]];
        yield 'type without type' => [ValidatorCode::MissingRuleParameter, new TypeRule(), []];
        yield 'type misspelled' => [ValidatorCode::InvalidRuleParameter, new TypeRule(), ['type' => 'interger']];
        yield 'instance_of without class' => [ValidatorCode::MissingRuleParameter, new InstanceOfRule(), []];
        yield 'instance_of of a missing class' => [ValidatorCode::InvalidRuleParameter, new InstanceOfRule(), ['class' => 'App\\Missing']];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[Test]
    #[DataProvider('misconfigurations')]
    public function misconfiguredRulesThrow(ValidatorCode $code, ValidationRule $rule, array $parameters): void
    {
        self::assertRefused($code, $rule, $parameters, 12);
    }

    #[Test]
    public function anInvalidBoundNamesTheFormatAndTheValue(): void
    {
        try {
            (new DateAfterRule())->validate('2024-01-02', self::context(['after' => 'tomorrow']));
            self::fail('An invalid bound was accepted.');
        } catch (\KaririCode\Validator\Exception\InvalidRuleException $exception) {
            self::assertStringEndsWith("must be a date in the format Y-m-d or a DateTimeInterface, 'tomorrow' given.", $exception->getMessage());
        }
    }
}
