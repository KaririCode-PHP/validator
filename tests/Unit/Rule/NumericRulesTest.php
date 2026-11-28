<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule;

use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Rule\Comparison\BetweenRule;
use KaririCode\Validator\Rule\Comparison\EqualToRule;
use KaririCode\Validator\Rule\Comparison\GreaterThanOrEqualRule;
use KaririCode\Validator\Rule\Comparison\GreaterThanRule;
use KaririCode\Validator\Rule\Comparison\LessThanOrEqualRule;
use KaririCode\Validator\Rule\Comparison\LessThanRule;
use KaririCode\Validator\Rule\Comparison\NotEqualToRule;
use KaririCode\Validator\Rule\Numeric\DecimalRule;
use KaririCode\Validator\Rule\Numeric\IntegerRule;
use KaririCode\Validator\Rule\Numeric\NegativeOrZeroRule;
use KaririCode\Validator\Rule\Numeric\NegativeRule;
use KaririCode\Validator\Rule\Numeric\PositiveRule;
use KaririCode\Validator\Rule\Numeric\RangeRule;
use KaririCode\Validator\Tests\Support\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

// ── Unit: NumericRulesTest ───────────────────────────────────────────
// The numeric and comparison rules, on integers, floats and numeric strings.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(IntegerRule::class)]
#[CoversClass(DecimalRule::class)]
#[CoversClass(PositiveRule::class)]
#[CoversClass(NegativeRule::class)]
#[CoversClass(NegativeOrZeroRule::class)]
#[CoversClass(RangeRule::class)]
#[CoversClass(EqualToRule::class)]
#[CoversClass(NotEqualToRule::class)]
#[CoversClass(GreaterThanRule::class)]
#[CoversClass(GreaterThanOrEqualRule::class)]
#[CoversClass(LessThanRule::class)]
#[CoversClass(LessThanOrEqualRule::class)]
#[CoversClass(BetweenRule::class)]
final class NumericRulesTest extends RuleTestCase
{
    /**
     * @return iterable<string, array{bool, ValidationRule, mixed, 3?: array<string, mixed>}>
     */
    public static function cases(): iterable
    {
        yield 'integer: int' => [true, new IntegerRule(), 12];
        yield 'integer: string' => [true, new IntegerRule(), '-3'];
        yield 'integer: padded string' => [true, new IntegerRule(), ' 12 '];
        yield 'integer: integral float' => [true, new IntegerRule(), 12.0];
        yield 'integer: fraction' => [false, new IntegerRule(), 12.5];
        yield 'integer: exponent' => [false, new IntegerRule(), '1e3'];
        yield 'integer: leading zero' => [false, new IntegerRule(), '012'];
        yield 'integer: true' => [false, new IntegerRule(), true];
        yield 'integer: beyond PHP_INT_MAX' => [false, new IntegerRule(), '9223372036854775808'];

        yield 'decimal: two places' => [true, new DecimalRule(), '1.25'];
        yield 'decimal: trailing zero ignored' => [true, new DecimalRule(), '1.250'];
        yield 'decimal: three places' => [false, new DecimalRule(), '1.505'];
        yield 'decimal: float' => [true, new DecimalRule(), 1.5];
        yield 'decimal: integer' => [true, new DecimalRule(), 2];
        yield 'decimal: integer string' => [true, new DecimalRule(), '2'];
        yield 'decimal: padded string' => [true, new DecimalRule(), ' 1.25 '];
        yield 'decimal: leading dot' => [true, new DecimalRule(), '.5'];
        yield 'decimal: trailing dot' => [true, new DecimalRule(), '5.'];
        yield 'decimal: negative exponent' => [false, new DecimalRule(), '1.5e-3'];
        yield 'decimal: negative exponent, four places' => [true, new DecimalRule(), '1.5e-3', ['places' => 4]];
        yield 'decimal: negative exponent, three places' => [false, new DecimalRule(), '1.5E-3', ['places' => 3]];
        yield 'decimal: positive exponent' => [true, new DecimalRule(), '1.555e+2', ['places' => 1]];
        yield 'decimal: upper-case exponent' => [true, new DecimalRule(), '1.5E+2', ['places' => 1]];
        yield 'decimal: exponent without a dot' => [false, new DecimalRule(), '5e-3', ['places' => 2]];
        yield 'decimal: positive exponent, too many places' => [false, new DecimalRule(), '1.5555e2', ['places' => 1]];
        yield 'decimal: small float' => [true, new DecimalRule(), 0.0015, ['places' => 4]];
        yield 'decimal: small float, too many places' => [false, new DecimalRule(), 0.0015, ['places' => 3]];
        yield 'decimal: zero places, integer' => [true, new DecimalRule(), 7, ['places' => 0]];
        yield 'decimal: zero places, fraction' => [false, new DecimalRule(), 7.5, ['places' => 0]];
        yield 'decimal: not a number' => [false, new DecimalRule(), 'abc'];
        yield 'decimal: true' => [false, new DecimalRule(), true];

        yield 'positive: one' => [true, new PositiveRule(), 1];
        yield 'positive: small' => [true, new PositiveRule(), 0.001];
        yield 'positive: string' => [true, new PositiveRule(), '5'];
        yield 'positive: zero' => [false, new PositiveRule(), 0];
        yield 'positive: negative' => [false, new PositiveRule(), -1];
        yield 'positive: infinity' => [false, new PositiveRule(), INF];
        yield 'positive: NAN' => [false, new PositiveRule(), NAN];
        yield 'positive: true' => [false, new PositiveRule(), true];
        yield 'negative: minus one' => [true, new NegativeRule(), -1];
        yield 'negative: string' => [true, new NegativeRule(), '-0.5'];
        yield 'negative: zero' => [false, new NegativeRule(), 0];
        yield 'negative: minus infinity' => [false, new NegativeRule(), -INF];
        yield 'negative_or_zero: zero' => [true, new NegativeOrZeroRule(), 0];
        yield 'negative_or_zero: string zero' => [true, new NegativeOrZeroRule(), '0'];
        yield 'negative_or_zero: negative' => [true, new NegativeOrZeroRule(), -1];
        yield 'negative_or_zero: positive' => [false, new NegativeOrZeroRule(), 0.0001];
        yield 'negative_or_zero: text' => [false, new NegativeOrZeroRule(), 'x'];

        yield 'range: at min' => [true, new RangeRule(), 18, ['min' => 18]];
        yield 'range: under min' => [false, new RangeRule(), 17.99, ['min' => 18]];
        yield 'range: at max' => [true, new RangeRule(), 130, ['max' => 130]];
        yield 'range: over max' => [false, new RangeRule(), 131, ['max' => 130]];
        yield 'range: within both' => [true, new RangeRule(), '50', ['min' => 18, 'max' => 130.5]];
        yield 'range: integers past 2^53' => [false, new RangeRule(), PHP_INT_MAX - 1, ['min' => PHP_INT_MAX]];
        yield 'range: not a number' => [false, new RangeRule(), 'x', ['min' => 1]];

        yield 'equal_to: same' => [true, new EqualToRule(), true, ['expected' => true]];
        yield 'equal_to: other type' => [false, new EqualToRule(), 1, ['expected' => '1']];
        yield 'equal_to: null expected' => [true, new EqualToRule(), null, ['expected' => null]];
        yield 'equal_to: null expected, value given' => [false, new EqualToRule(), 'x', ['expected' => null]];
        yield 'not_equal_to: different' => [true, new NotEqualToRule(), 'ana', ['expected' => 'admin']];
        yield 'not_equal_to: same' => [false, new NotEqualToRule(), 'admin', ['expected' => 'admin']];
        yield 'not_equal_to: other type' => [true, new NotEqualToRule(), 1, ['expected' => '1']];

        $zero = ['threshold' => 0];
        yield 'greater_than: above' => [true, new GreaterThanRule(), '0.5', $zero];
        yield 'greater_than: equal' => [false, new GreaterThanRule(), 0, $zero];
        yield 'greater_than: text' => [false, new GreaterThanRule(), 'x', $zero];
        yield 'greater_than_or_equal: equal' => [true, new GreaterThanOrEqualRule(), 0, $zero];
        yield 'greater_than_or_equal: below' => [false, new GreaterThanOrEqualRule(), -1, $zero];
        yield 'greater_than_or_equal: text' => [false, new GreaterThanOrEqualRule(), 'x', $zero];
        yield 'less_than: below' => [true, new LessThanRule(), -0.5, $zero];
        yield 'less_than: equal' => [false, new LessThanRule(), 0, $zero];
        yield 'less_than: text' => [false, new LessThanRule(), 'x', $zero];
        yield 'less_than_or_equal: equal' => [true, new LessThanOrEqualRule(), 0, $zero];
        yield 'less_than_or_equal: above' => [false, new LessThanOrEqualRule(), 1, $zero];
        yield 'less_than_or_equal: text' => [false, new LessThanOrEqualRule(), 'x', $zero];

        $unit = ['min' => 0, 'max' => 1];
        yield 'between: inside' => [true, new BetweenRule(), 0.5, $unit];
        yield 'between: at min' => [false, new BetweenRule(), 0, $unit];
        yield 'between: at max' => [false, new BetweenRule(), 1, $unit];
        yield 'between: below' => [false, new BetweenRule(), -1, $unit];
        yield 'between: above' => [false, new BetweenRule(), 2, $unit];
        yield 'between: text' => [false, new BetweenRule(), 'x', $unit];
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
    public function violationsNameTheirCodes(): void
    {
        self::assertViolation('numeric.integer', new IntegerRule());
        self::assertViolation('numeric.positive', new PositiveRule());
        self::assertViolation('numeric.negative', new NegativeRule());
        self::assertViolation('numeric.negative_or_zero', new NegativeOrZeroRule());
        self::assertViolation('numeric.range_min', new RangeRule(), ['min' => 1]);
        self::assertViolation('numeric.range_max', new RangeRule(), ['max' => 1]);
        self::assertViolation('numeric.range', new RangeRule(), ['min' => 0, 'max' => 1]);
        self::assertViolation('comparison.equal_to', new EqualToRule());
        self::assertViolation('comparison.not_equal_to', new NotEqualToRule());
        self::assertViolation('comparison.greater_than', new GreaterThanRule());
        self::assertViolation('comparison.greater_than_or_equal', new GreaterThanOrEqualRule());
        self::assertViolation('comparison.less_than', new LessThanRule());
        self::assertViolation('comparison.less_than_or_equal', new LessThanOrEqualRule());
        self::assertViolation('comparison.between', new BetweenRule());
    }

    #[Test]
    public function theDecimalMessageNamesThePlacesApplied(): void
    {
        self::assertViolation('numeric.decimal', new DecimalRule());
        self::assertSame(['places' => 2], (new DecimalRule())->violation(self::context())->parameters);
        self::assertSame(['places' => 4], (new DecimalRule())->violation(self::context(['places' => 4]))->parameters);
    }

    /**
     * @return iterable<string, array{ValidatorCode, ValidationRule, array<string, mixed>}>
     */
    public static function misconfigurations(): iterable
    {
        yield 'decimal with negative places' => [ValidatorCode::InvalidRuleParameter, new DecimalRule(), ['places' => -1]];
        yield 'decimal with string places' => [ValidatorCode::InvalidRuleParameter, new DecimalRule(), ['places' => '2']];
        yield 'range without bounds' => [ValidatorCode::MissingRuleParameter, new RangeRule(), []];
        yield 'range with a string bound' => [ValidatorCode::InvalidRuleParameter, new RangeRule(), ['min' => '1']];
        yield 'range with an infinite bound' => [ValidatorCode::InvalidRuleParameter, new RangeRule(), ['max' => INF]];
        yield 'equal_to without expected' => [ValidatorCode::MissingRuleParameter, new EqualToRule(), []];
        yield 'not_equal_to without expected' => [ValidatorCode::MissingRuleParameter, new NotEqualToRule(), []];
        yield 'greater_than without threshold' => [ValidatorCode::MissingRuleParameter, new GreaterThanRule(), []];
        yield 'less_than with a string threshold' => [ValidatorCode::InvalidRuleParameter, new LessThanRule(), ['threshold' => '0']];
        yield 'between without max' => [ValidatorCode::MissingRuleParameter, new BetweenRule(), ['min' => 0]];
        yield 'between without min' => [ValidatorCode::MissingRuleParameter, new BetweenRule(), ['max' => 0]];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[Test]
    #[DataProvider('misconfigurations')]
    public function misconfiguredRulesThrow(ValidatorCode $code, ValidationRule $rule, array $parameters): void
    {
        self::assertRefused($code, $rule, $parameters, 'not a number');
    }
}
