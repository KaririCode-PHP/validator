<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule;

use ArrayObject;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Rule\Collection\CountRule;
use KaririCode\Validator\Rule\Collection\EachRule;
use KaririCode\Validator\Rule\Collection\KeyExistsRule;
use KaririCode\Validator\Rule\Collection\UniqueElementsRule;
use KaririCode\Validator\Rule\CrossField\DifferentFromRule;
use KaririCode\Validator\Rule\CrossField\RequiredWithoutRule;
use KaririCode\Validator\Rule\CrossField\RequiredWithRule;
use KaririCode\Validator\Rule\CrossField\SameAsRule;
use KaririCode\Validator\Rule\String\EmailRule;
use KaririCode\Validator\Tests\Support\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

// ── Unit: CollectionAndCrossFieldRulesTest ───────────────────────────
// Rules on arrays, and rules that read other fields.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(CountRule::class)]
#[CoversClass(UniqueElementsRule::class)]
#[CoversClass(EachRule::class)]
#[CoversClass(KeyExistsRule::class)]
#[CoversClass(SameAsRule::class)]
#[CoversClass(DifferentFromRule::class)]
#[CoversClass(RequiredWithRule::class)]
#[CoversClass(RequiredWithoutRule::class)]
final class CollectionAndCrossFieldRulesTest extends RuleTestCase
{
    /**
     * @return iterable<string, array{bool, ValidationRule, mixed, array<string, mixed>, 4?: array<array-key, mixed>}>
     */
    public static function cases(): iterable
    {
        yield 'count: at min' => [true, new CountRule(), [1], ['min' => 1]];
        yield 'count: under min' => [false, new CountRule(), [], ['min' => 1]];
        yield 'count: at max' => [true, new CountRule(), [1, 2], ['max' => 2]];
        yield 'count: over max' => [false, new CountRule(), [1, 2, 3], ['max' => 2]];
        yield 'count: Countable' => [true, new CountRule(), new ArrayObject([1, 2]), ['min' => 1, 'max' => 2]];
        yield 'count: a string' => [false, new CountRule(), 'abc', ['min' => 1]];

        $object = new stdClass();
        yield 'unique: distinct' => [true, new UniqueElementsRule(), [1, 2, 3], []];
        yield 'unique: repeated' => [false, new UniqueElementsRule(), [1, 2, 1], []];
        yield 'unique: same digits, three types' => [true, new UniqueElementsRule(), [1, '1', 1.0], []];
        yield 'unique: repeated arrays' => [false, new UniqueElementsRule(), [['a' => 1], ['a' => 1]], []];
        yield 'unique: equal objects' => [true, new UniqueElementsRule(), [new stdClass(), new stdClass()], []];
        yield 'unique: one object twice' => [false, new UniqueElementsRule(), [$object, $object], []];
        yield 'unique: empty' => [true, new UniqueElementsRule(), [], []];
        yield 'unique: a string' => [false, new UniqueElementsRule(), 'aa', []];

        $emails = new EachRule(new EmailRule());
        yield 'each: all valid' => [true, $emails, ['a@example.com', 'b@example.com'], []];
        yield 'each: one invalid' => [false, $emails, ['a@example.com', 'b'], []];
        yield 'each: empty' => [true, $emails, [], []];
        yield 'each: a string' => [false, $emails, 'a@example.com', []];

        yield 'key_exists: key holding null' => [true, new KeyExistsRule(), ['mode' => null], ['key' => 'mode']];
        yield 'key_exists: missing key' => [false, new KeyExistsRule(), ['other' => 1], ['key' => 'mode']];
        yield 'key_exists: numeric key as a string' => [true, new KeyExistsRule(), [1 => 'a'], ['key' => '1']];
        yield 'key_exists: integer key' => [true, new KeyExistsRule(), ['a', 'b'], ['key' => 1]];
        yield 'key_exists: a string' => [false, new KeyExistsRule(), 'mode', ['key' => 'mode']];

        $password = ['password' => 'secret', 'user' => ['password' => 'nested']];
        yield 'same_as: equal' => [true, new SameAsRule(), 'secret', ['other' => 'password'], $password];
        yield 'same_as: different' => [false, new SameAsRule(), 'Secret', ['other' => 'password'], $password];
        yield 'same_as: other absent' => [false, new SameAsRule(), null, ['other' => 'missing'], $password];
        yield 'same_as: nested other' => [true, new SameAsRule(), 'nested', ['other' => 'user.password'], $password];
        yield 'different_from: different' => [true, new DifferentFromRule(), 'new', ['other' => 'password'], $password];
        yield 'different_from: equal' => [false, new DifferentFromRule(), 'secret', ['other' => 'password'], $password];
        yield 'different_from: other absent' => [true, new DifferentFromRule(), null, ['other' => 'missing'], $password];

        $country = ['other' => 'country'];
        yield 'required_with: other filled, this empty' => [false, new RequiredWithRule(), '', $country, ['country' => 'BR']];
        yield 'required_with: both filled' => [true, new RequiredWithRule(), 'CE', $country, ['country' => 'BR']];
        yield 'required_with: other blank' => [true, new RequiredWithRule(), '', $country, ['country' => ' ']];
        yield 'required_with: other absent' => [true, new RequiredWithRule(), null, $country, []];
        $email = ['other' => 'email'];
        yield 'required_without: neither filled' => [false, new RequiredWithoutRule(), '', $email, ['email' => '']];
        yield 'required_without: other filled' => [true, new RequiredWithoutRule(), '', $email, ['email' => 'a@example.com']];
        yield 'required_without: this filled' => [true, new RequiredWithoutRule(), '+5588999', $email, []];
    }

    /**
     * @param array<string, mixed>    $parameters
     * @param array<array-key, mixed> $data
     */
    #[Test]
    #[DataProvider('cases')]
    public function rulesAcceptAndRejectValues(bool $expected, ValidationRule $rule, mixed $value, array $parameters, array $data = []): void
    {
        self::assertRule($expected, $rule, $value, $parameters, $data);
    }

    #[Test]
    public function violationsNameTheirCodes(): void
    {
        self::assertViolation('collection.count_min', new CountRule(), ['min' => 1]);
        self::assertViolation('collection.count_max', new CountRule(), ['max' => 1]);
        self::assertViolation('collection.count', new CountRule(), ['min' => 1, 'max' => 2]);
        self::assertViolation('collection.unique', new UniqueElementsRule());
        self::assertViolation('collection.each', new EachRule(new EmailRule()));
        self::assertViolation('collection.key_exists', new KeyExistsRule());
        self::assertViolation('crossfield.same_as', new SameAsRule());
        self::assertViolation('crossfield.different_from', new DifferentFromRule());
        self::assertViolation('crossfield.required_with', new RequiredWithRule());
        self::assertViolation('crossfield.required_without', new RequiredWithoutRule());
    }

    /**
     * @return iterable<string, array{ValidatorCode, ValidationRule, array<string, mixed>}>
     */
    public static function misconfigurations(): iterable
    {
        yield 'count without bounds' => [ValidatorCode::MissingRuleParameter, new CountRule(), []];
        yield 'count with a float bound' => [ValidatorCode::InvalidRuleParameter, new CountRule(), ['max' => 2.0]];
        yield 'key_exists without key' => [ValidatorCode::MissingRuleParameter, new KeyExistsRule(), []];
        yield 'key_exists with a float key' => [ValidatorCode::InvalidRuleParameter, new KeyExistsRule(), ['key' => 1.5]];
        yield 'same_as without other' => [ValidatorCode::MissingRuleParameter, new SameAsRule(), []];
        yield 'different_from with a list' => [ValidatorCode::InvalidRuleParameter, new DifferentFromRule(), ['other' => ['a']]];
        yield 'required_with without other' => [ValidatorCode::MissingRuleParameter, new RequiredWithRule(), []];
        yield 'required_without without other' => [ValidatorCode::MissingRuleParameter, new RequiredWithoutRule(), []];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[Test]
    #[DataProvider('misconfigurations')]
    public function misconfiguredRulesThrow(ValidatorCode $code, ValidationRule $rule, array $parameters): void
    {
        self::assertRefused($code, $rule, $parameters, 'a string');
    }
}
