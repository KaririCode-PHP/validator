<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule;

use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Rule\String\AlphanumericRule;
use KaririCode\Validator\Rule\String\AlphaRule;
use KaririCode\Validator\Rule\String\EmailRule;
use KaririCode\Validator\Rule\String\EndsWithRule;
use KaririCode\Validator\Rule\String\HostnameRule;
use KaririCode\Validator\Rule\String\IpRule;
use KaririCode\Validator\Rule\String\JsonRule;
use KaririCode\Validator\Rule\String\LengthRule;
use KaririCode\Validator\Rule\String\NotEmptyRule;
use KaririCode\Validator\Rule\String\PatternRule;
use KaririCode\Validator\Rule\String\SlugRule;
use KaririCode\Validator\Rule\String\StartsWithRule;
use KaririCode\Validator\Rule\String\UlidRule;
use KaririCode\Validator\Rule\String\UrlRule;
use KaririCode\Validator\Rule\String\UuidRule;
use KaririCode\Validator\Tests\Support\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

// ── Unit: StringRulesTest ────────────────────────────────────────────
// The string rules, anchored at both ends and strict about types.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(AlphaRule::class)]
#[CoversClass(AlphanumericRule::class)]
#[CoversClass(EmailRule::class)]
#[CoversClass(UrlRule::class)]
#[CoversClass(LengthRule::class)]
#[CoversClass(PatternRule::class)]
#[CoversClass(NotEmptyRule::class)]
#[CoversClass(UuidRule::class)]
#[CoversClass(UlidRule::class)]
#[CoversClass(IpRule::class)]
#[CoversClass(JsonRule::class)]
#[CoversClass(HostnameRule::class)]
#[CoversClass(SlugRule::class)]
#[CoversClass(StartsWithRule::class)]
#[CoversClass(EndsWithRule::class)]
final class StringRulesTest extends RuleTestCase
{
    /**
     * @return iterable<string, array{bool, ValidationRule, mixed, 3?: array<string, mixed>}>
     */
    public static function cases(): iterable
    {
        yield 'alpha: accented' => [true, new AlphaRule(), 'José'];
        yield 'alpha: greek' => [true, new AlphaRule(), 'Ωmega'];
        yield 'alpha: digit' => [false, new AlphaRule(), 'abc1'];
        yield 'alpha: space' => [false, new AlphaRule(), 'a b'];
        yield 'alpha: empty' => [false, new AlphaRule(), ''];
        yield 'alpha: trailing newline' => [false, new AlphaRule(), "abc\n"];
        yield 'alpha: invalid UTF-8' => [false, new AlphaRule(), "\xff"];
        yield 'alpha: integer' => [false, new AlphaRule(), 5];

        yield 'alphanumeric: letters and digits' => [true, new AlphanumericRule(), 'Ação2'];
        yield 'alphanumeric: hyphen' => [false, new AlphanumericRule(), 'a-b'];
        yield 'alphanumeric: empty' => [false, new AlphanumericRule(), ''];
        yield 'alphanumeric: trailing newline' => [false, new AlphanumericRule(), "a1\n"];
        yield 'alphanumeric: integer' => [false, new AlphanumericRule(), 12];

        $longest = str_repeat('a', 64) . '@' . str_repeat('b', 63) . '.' . str_repeat('c', 63) . '.' . str_repeat('d', 57) . '.com';
        yield 'email: plain' => [true, new EmailRule(), 'ana@example.com'];
        yield 'email: 254 characters' => [true, new EmailRule(), $longest];
        yield 'email: 255 characters' => [false, new EmailRule(), 'a' . $longest];
        yield 'email: no dot in domain' => [false, new EmailRule(), 'ana@example'];
        yield 'email: two at signs' => [false, new EmailRule(), 'ana@@example.com'];
        yield 'email: trailing newline' => [false, new EmailRule(), "ana@example.com\n"];
        yield 'email: integer' => [false, new EmailRule(), 5];

        yield 'url: https' => [true, new UrlRule(), 'https://example.com/a?b=c'];
        yield 'url: http' => [true, new UrlRule(), 'http://example.com'];
        yield 'url: upper-case scheme' => [true, new UrlRule(), 'HTTPS://EXAMPLE.COM'];
        yield 'url: ftp by default' => [false, new UrlRule(), 'ftp://example.com'];
        yield 'url: ftp allowed' => [true, new UrlRule(), 'ftp://example.com', ['schemes' => ['ftp']]];
        yield 'url: allowed schemes in upper case' => [true, new UrlRule(), 'https://example.com', ['schemes' => ['HTTPS']]];
        yield 'url: https not allowed' => [false, new UrlRule(), 'https://example.com', ['schemes' => ['ftp']]];
        yield 'url: javascript' => [false, new UrlRule(), 'javascript://comment%0Aalert(1)'];
        yield 'url: file' => [false, new UrlRule(), 'file:///etc/passwd'];
        yield 'url: not a url' => [false, new UrlRule(), 'example dot com'];
        yield 'url: allowed scheme, invalid URL' => [false, new UrlRule(), 'http://exa mple.com'];
        yield 'url: integer' => [false, new UrlRule(), 5];

        yield 'length: at min' => [true, new LengthRule(), 'abc', ['min' => 3]];
        yield 'length: under min' => [false, new LengthRule(), 'ab', ['min' => 3]];
        yield 'length: at max' => [true, new LengthRule(), 'abcde', ['max' => 5]];
        yield 'length: over max' => [false, new LengthRule(), 'abcdef', ['max' => 5]];
        yield 'length: characters, not bytes' => [true, new LengthRule(), 'ação', ['min' => 4, 'max' => 4]];
        yield 'length: within both' => [true, new LengthRule(), 'abcd', ['min' => 3, 'max' => 5]];
        yield 'length: integer' => [false, new LengthRule(), 12345, ['min' => 1]];

        $code = ['pattern' => '/\A[A-Z]{3}-\d{4}\z/'];
        yield 'pattern: matches' => [true, new PatternRule(), 'ABC-1234', $code];
        yield 'pattern: does not match' => [false, new PatternRule(), 'abc-1234', $code];
        yield 'pattern: integer' => [false, new PatternRule(), 1234, $code];
        yield 'pattern: invalid UTF-8 under /u' => [false, new PatternRule(), "\xff", ['pattern' => '/./u']];

        yield 'not_empty: text' => [true, new NotEmptyRule(), ' a '];
        yield 'not_empty: empty' => [false, new NotEmptyRule(), ''];
        yield 'not_empty: whitespace' => [false, new NotEmptyRule(), " \n\t"];
        yield 'not_empty: integer' => [false, new NotEmptyRule(), 5];

        yield 'uuid: v4' => [true, new UuidRule(), '550e8400-e29b-41d4-a716-446655440000'];
        yield 'uuid: upper case' => [true, new UuidRule(), '550E8400-E29B-41D4-A716-446655440000'];
        yield 'uuid: nil' => [true, new UuidRule(), '00000000-0000-0000-0000-000000000000'];
        yield 'uuid: no hyphens' => [false, new UuidRule(), '550e8400e29b41d4a716446655440000'];
        yield 'uuid: not hexadecimal' => [false, new UuidRule(), 'g50e8400-e29b-41d4-a716-446655440000'];
        yield 'uuid: trailing newline' => [false, new UuidRule(), "550e8400-e29b-41d4-a716-446655440000\n"];
        yield 'uuid: integer' => [false, new UuidRule(), 5];

        yield 'ulid: canonical' => [true, new UlidRule(), '01ARZ3NDEKTSV4RRFFQ69G5FAV'];
        yield 'ulid: lower case' => [true, new UlidRule(), '01arz3ndektsv4rrffq69g5fav'];
        yield 'ulid: largest first character' => [true, new UlidRule(), '7ZZZZZZZZZZZZZZZZZZZZZZZZZ'];
        yield 'ulid: timestamp overflow' => [false, new UlidRule(), '81ARZ3NDEKTSV4RRFFQ69G5FAV'];
        yield 'ulid: excluded letter' => [false, new UlidRule(), '01ARZ3NDEKTSV4RRFFQ69G5FAI'];
        yield 'ulid: 25 characters' => [false, new UlidRule(), '01ARZ3NDEKTSV4RRFFQ69G5FA'];
        yield 'ulid: integer' => [false, new UlidRule(), 5];

        yield 'ip: v4' => [true, new IpRule(), '192.168.0.1'];
        yield 'ip: v6' => [true, new IpRule(), '::1'];
        yield 'ip: out of range' => [false, new IpRule(), '256.0.0.1'];
        yield 'ip: v4 only, v4 given' => [true, new IpRule(), '10.0.0.1', ['version' => 4]];
        yield 'ip: v4 only, v6 given' => [false, new IpRule(), '::1', ['version' => 4]];
        yield 'ip: v6 as a string, v6 given' => [true, new IpRule(), '::1', ['version' => '6']];
        yield 'ip: v6 only, v4 given' => [false, new IpRule(), '10.0.0.1', ['version' => 6]];
        yield 'ip: v4 as a string, v4 given' => [true, new IpRule(), '10.0.0.1', ['version' => '4']];
        yield 'ip: integer' => [false, new IpRule(), 5];

        yield 'json: object' => [true, new JsonRule(), '{"a":1}'];
        yield 'json: scalar' => [true, new JsonRule(), '"x"'];
        yield 'json: unquoted key' => [false, new JsonRule(), '{a:1}'];
        yield 'json: empty' => [false, new JsonRule(), ''];
        yield 'json: integer' => [false, new JsonRule(), 5];

        $label63 = str_repeat('a', 63);
        yield 'hostname: domain' => [true, new HostnameRule(), 'example.com'];
        yield 'hostname: hyphen inside' => [true, new HostnameRule(), 'a-b.example.com'];
        yield 'hostname: single label' => [true, new HostnameRule(), 'localhost'];
        yield 'hostname: 253 characters' => [true, new HostnameRule(), "{$label63}.{$label63}.{$label63}." . str_repeat('b', 61)];
        yield 'hostname: 254 characters' => [false, new HostnameRule(), "{$label63}.{$label63}.{$label63}." . str_repeat('b', 62)];
        yield 'hostname: 64-character label' => [false, new HostnameRule(), $label63 . 'a.com'];
        yield 'hostname: leading hyphen' => [false, new HostnameRule(), '-a.com'];
        yield 'hostname: trailing hyphen' => [false, new HostnameRule(), 'a-.com'];
        yield 'hostname: hyphens around a later label' => [false, new HostnameRule(), 'example.-bad-.com'];
        yield 'hostname: empty label' => [false, new HostnameRule(), 'example..com'];
        yield 'hostname: underscore' => [false, new HostnameRule(), 'exa_mple.com'];
        yield 'hostname: trailing newline' => [false, new HostnameRule(), "example.com\n"];
        yield 'hostname: integer' => [false, new HostnameRule(), 5];

        yield 'slug: words' => [true, new SlugRule(), 'my-post-2'];
        yield 'slug: upper case' => [false, new SlugRule(), 'My-Post'];
        yield 'slug: leading hyphen' => [false, new SlugRule(), '-a'];
        yield 'slug: trailing hyphen' => [false, new SlugRule(), 'a-'];
        yield 'slug: double hyphen' => [false, new SlugRule(), 'a--b'];
        yield 'slug: underscore' => [false, new SlugRule(), 'a_b'];
        yield 'slug: trailing newline' => [false, new SlugRule(), "a\n"];
        yield 'slug: integer' => [false, new SlugRule(), 5];

        yield 'starts_with: prefix' => [true, new StartsWithRule(), '+5588999', ['prefix' => '+55']];
        yield 'starts_with: no prefix' => [false, new StartsWithRule(), '5588999', ['prefix' => '+55']];
        yield 'starts_with: integer' => [false, new StartsWithRule(), 55, ['prefix' => '5']];
        yield 'ends_with: suffix' => [true, new EndsWithRule(), 'ana@example.com', ['suffix' => '@example.com']];
        yield 'ends_with: other suffix' => [false, new EndsWithRule(), 'ana@example.org', ['suffix' => '@example.com']];
        yield 'ends_with: integer' => [false, new EndsWithRule(), 55, ['suffix' => '5']];
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
        self::assertViolation('string.alpha', new AlphaRule());
        self::assertViolation('string.alphanumeric', new AlphanumericRule());
        self::assertViolation('string.email', new EmailRule());
        self::assertViolation('string.url', new UrlRule());
        self::assertViolation('string.pattern', new PatternRule());
        self::assertViolation('string.not_empty', new NotEmptyRule());
        self::assertViolation('string.uuid', new UuidRule());
        self::assertViolation('string.ulid', new UlidRule());
        self::assertViolation('string.ip', new IpRule());
        self::assertViolation('string.json', new JsonRule());
        self::assertViolation('string.hostname', new HostnameRule());
        self::assertViolation('string.slug', new SlugRule());
        self::assertViolation('string.starts_with', new StartsWithRule());
        self::assertViolation('string.ends_with', new EndsWithRule());
    }

    #[Test]
    public function aLengthMessageNamesOnlyTheBoundsGiven(): void
    {
        self::assertViolation('string.length_min', new LengthRule(), ['min' => 3]);
        self::assertViolation('string.length_max', new LengthRule(), ['max' => 3]);
        self::assertViolation('string.length', new LengthRule(), ['min' => 1, 'max' => 3]);
    }

    /**
     * @return iterable<string, array{ValidatorCode, ValidationRule, array<string, mixed>}>
     */
    public static function misconfigurations(): iterable
    {
        yield 'length without bounds' => [ValidatorCode::MissingRuleParameter, new LengthRule(), []];
        yield 'length with a string bound' => [ValidatorCode::InvalidRuleParameter, new LengthRule(), ['min' => '3']];
        yield 'length with a string max' => [ValidatorCode::InvalidRuleParameter, new LengthRule(), ['max' => '3']];
        yield 'pattern missing' => [ValidatorCode::MissingRuleParameter, new PatternRule(), []];
        yield 'pattern empty' => [ValidatorCode::InvalidRuleParameter, new PatternRule(), ['pattern' => '']];
        yield 'pattern that does not compile' => [ValidatorCode::InvalidRuleParameter, new PatternRule(), ['pattern' => '/(/']];
        yield 'ip version 5' => [ValidatorCode::InvalidRuleParameter, new IpRule(), ['version' => 5]];
        yield 'ip version v4' => [ValidatorCode::InvalidRuleParameter, new IpRule(), ['version' => 'v4']];
        yield 'ip version as a list' => [ValidatorCode::InvalidRuleParameter, new IpRule(), ['version' => [4]]];
        yield 'url schemes as a string' => [ValidatorCode::InvalidRuleParameter, new UrlRule(), ['schemes' => 'https']];
        yield 'url schemes empty' => [ValidatorCode::InvalidRuleParameter, new UrlRule(), ['schemes' => []]];
        yield 'url schemes not strings' => [ValidatorCode::InvalidRuleParameter, new UrlRule(), ['schemes' => ['https', 1]]];
        yield 'url schemes as a map' => [ValidatorCode::InvalidRuleParameter, new UrlRule(), ['schemes' => ['a' => 'https']]];
        yield 'starts_with empty' => [ValidatorCode::InvalidRuleParameter, new StartsWithRule(), ['prefix' => '']];
        yield 'starts_with missing' => [ValidatorCode::MissingRuleParameter, new StartsWithRule(), []];
        yield 'ends_with empty' => [ValidatorCode::InvalidRuleParameter, new EndsWithRule(), ['suffix' => '']];
    }

    /**
     * Misconfiguration is reported whatever the value, so a wrong rule
     * cannot hide behind input of another type.
     *
     * @param array<string, mixed> $parameters
     */
    #[Test]
    #[DataProvider('misconfigurations')]
    public function misconfiguredRulesThrow(ValidatorCode $code, ValidationRule $rule, array $parameters): void
    {
        self::assertRefused($code, $rule, $parameters);
    }

    #[Test]
    public function parameterErrorsNameWhatWasGiven(): void
    {
        foreach ([
            [new IpRule(), ['version' => 5], 'must be 4 or 6, 5 given.'],
            [new IpRule(), ['version' => [4]], 'must be 4 or 6, array given.'],
            [new PatternRule(), ['pattern' => ''], 'must be a regular expression.'],
            [new PatternRule(), ['pattern' => '/(/'], 'must be a regular expression that compiles.'],
        ] as [$rule, $parameters, $ending]) {
            try {
                $rule->validate('value', self::context($parameters));
                self::fail('A misconfiguration was accepted.');
            } catch (\KaririCode\Validator\Exception\InvalidRuleException $exception) {
                self::assertStringEndsWith($ending, $exception->getMessage());
            }
        }
    }

    #[Test]
    public function boundsAreCheckedEvenForValuesOfAnotherType(): void
    {
        self::assertRefused(ValidatorCode::MissingRuleParameter, new LengthRule(), [], 12);
        self::assertRefused(ValidatorCode::InvalidRuleParameter, new UrlRule(), ['schemes' => []], 12);
        self::assertRefused(ValidatorCode::InvalidRuleParameter, new StartsWithRule(), ['prefix' => ''], 12);
        self::assertRefused(ValidatorCode::InvalidRuleParameter, new IpRule(), ['version' => 5], 12);
        self::assertRefused(ValidatorCode::MissingRuleParameter, new PatternRule(), [], 12);
    }
}
