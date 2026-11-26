<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\String;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\String\AlphanumericRule;
use KaririCode\Validator\Rule\String\AlphaRule;
use KaririCode\Validator\Rule\String\EndsWithRule;
use KaririCode\Validator\Rule\String\HostnameRule;
use KaririCode\Validator\Rule\String\IpRule;
use KaririCode\Validator\Rule\String\JsonRule;
use KaririCode\Validator\Rule\String\SlugRule;
use KaririCode\Validator\Rule\String\StartsWithRule;
use KaririCode\Validator\Rule\String\UlidRule;
use KaririCode\Validator\Rule\String\UuidRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlphaRule::class)]
#[CoversClass(AlphanumericRule::class)]
#[CoversClass(UuidRule::class)]
#[CoversClass(UlidRule::class)]
#[CoversClass(IpRule::class)]
#[CoversClass(JsonRule::class)]
#[CoversClass(HostnameRule::class)]
#[CoversClass(SlugRule::class)]
#[CoversClass(StartsWithRule::class)]
#[CoversClass(EndsWithRule::class)]
final class NewStringRulesTest extends TestCase
{
    private function ctx(array $params = []): ValidationContextImpl
    {
        $ctx = ValidationContextImpl::create([])->withField('test');

        return $params !== [] ? $ctx->withParameters($params) : $ctx;
    }

    // ── Alpha ──
    public function testAlphaAcceptsLetters(): void
    {
        $rule = new AlphaRule();
        $this->assertTrue($rule->validate('Hello', $this->ctx()));
        $this->assertTrue($rule->validate('Ação', $this->ctx())); // Unicode
        $this->assertFalse($rule->validate('Hello123', $this->ctx()));
        $this->assertFalse($rule->validate('', $this->ctx()));
        $this->assertFalse($rule->validate(123, $this->ctx()));
    }

    // ── Alphanumeric ──
    public function testAlphanumericAcceptsLettersAndDigits(): void
    {
        $rule = new AlphanumericRule();
        $this->assertTrue($rule->validate('Hello123', $this->ctx()));
        $this->assertTrue($rule->validate('Ação2024', $this->ctx()));
        $this->assertFalse($rule->validate('Hello 123', $this->ctx()));
        $this->assertFalse($rule->validate('hello-world', $this->ctx()));
    }

    // ── UUID ──
    public function testUuidValidation(): void
    {
        $rule = new UuidRule();
        $this->assertTrue($rule->validate('550e8400-e29b-41d4-a716-446655440000', $this->ctx()));
        $this->assertTrue($rule->validate('01929E3C-08E8-7B2D-A5F5-9B1A2D3E4F5A', $this->ctx()));
        $this->assertFalse($rule->validate('not-a-uuid', $this->ctx()));
        $this->assertFalse($rule->validate('550e8400-e29b-41d4-a716', $this->ctx()));
        $this->assertFalse($rule->validate(123, $this->ctx()));
    }

    // ── ULID ──
    public function testUlidValidation(): void
    {
        $rule = new UlidRule();
        $this->assertTrue($rule->validate('01ARZ3NDEKTSV4RRFFQ69G5FAV', $this->ctx()));
        $this->assertTrue($rule->validate('01arz3ndektsv4rrffq69g5fav', $this->ctx())); // lowercase OK
        $this->assertFalse($rule->validate('not-a-ulid', $this->ctx()));
        $this->assertFalse($rule->validate('01ARZ3NDEK', $this->ctx())); // too short
    }

    // ── IP ──
    public function testIpValidation(): void
    {
        $rule = new IpRule();
        $this->assertTrue($rule->validate('192.168.1.1', $this->ctx()));
        $this->assertTrue($rule->validate('::1', $this->ctx()));
        $this->assertFalse($rule->validate('999.999.999.999', $this->ctx()));
        $this->assertFalse($rule->validate('not-ip', $this->ctx()));
    }

    public function testIpV4Only(): void
    {
        $rule = new IpRule();
        $ctx = $this->ctx(['version' => 4]);
        $this->assertTrue($rule->validate('192.168.1.1', $ctx));
        $this->assertFalse($rule->validate('::1', $ctx));
    }

    public function testIpV6Only(): void
    {
        $rule = new IpRule();
        $ctx = $this->ctx(['version' => 6]);
        $this->assertTrue($rule->validate('::1', $ctx));
        $this->assertFalse($rule->validate('192.168.1.1', $ctx));
    }

    // ── JSON ──
    public function testJsonValidation(): void
    {
        $rule = new JsonRule();
        $this->assertTrue($rule->validate('{"key":"value"}', $this->ctx()));
        $this->assertTrue($rule->validate('[1,2,3]', $this->ctx()));
        $this->assertTrue($rule->validate('"string"', $this->ctx()));
        $this->assertTrue($rule->validate('42', $this->ctx()));
        $this->assertFalse($rule->validate('{invalid}', $this->ctx()));
        $this->assertFalse($rule->validate('', $this->ctx()));
    }

    // ── Hostname ──
    public function testHostnameValidation(): void
    {
        $rule = new HostnameRule();
        $this->assertTrue($rule->validate('example.com', $this->ctx()));
        $this->assertTrue($rule->validate('sub.domain.example.com', $this->ctx()));
        $this->assertTrue($rule->validate('localhost', $this->ctx()));
        $this->assertFalse($rule->validate('-invalid.com', $this->ctx()));
        $this->assertFalse($rule->validate('', $this->ctx()));
    }

    // ── Slug ──
    public function testSlugValidation(): void
    {
        $rule = new SlugRule();
        $this->assertTrue($rule->validate('my-cool-slug', $this->ctx()));
        $this->assertTrue($rule->validate('simple', $this->ctx()));
        $this->assertTrue($rule->validate('post123', $this->ctx()));
        $this->assertFalse($rule->validate('My-Slug', $this->ctx())); // uppercase
        $this->assertFalse($rule->validate('slug--double', $this->ctx()));
        $this->assertFalse($rule->validate('-leading', $this->ctx()));
    }

    // ── StartsWith ──
    public function testStartsWithValidation(): void
    {
        $rule = new StartsWithRule();
        $ctx = $this->ctx(['prefix' => 'https://']);
        $this->assertTrue($rule->validate('https://example.com', $ctx));
        $this->assertFalse($rule->validate('http://example.com', $ctx));
        $this->assertFalse($rule->validate(123, $ctx));
    }

    // ── EndsWith ──
    public function testEndsWithValidation(): void
    {
        $rule = new EndsWithRule();
        $ctx = $this->ctx(['suffix' => '.php']);
        $this->assertTrue($rule->validate('index.php', $ctx));
        $this->assertFalse($rule->validate('index.html', $ctx));
        $this->assertFalse($rule->validate(123, $ctx));
    }

    // ── Error Codes ──
    public function testIpStringVersion(): void
    {
        $rule = new IpRule();
        $this->assertTrue($rule->validate('192.168.1.1', $this->ctx(['version' => '4'])));
        $this->assertFalse($rule->validate('::1', $this->ctx(['version' => '4'])));
        $this->assertTrue($rule->validate('::1', $this->ctx(['version' => '6'])));
        $this->assertFalse($rule->validate('192.168.1.1', $this->ctx(['version' => '6'])));
    }

    public function testIpNonStringReturnsFalse(): void
    {
        $this->assertFalse(new IpRule()->validate(42, $this->ctx()));
        $this->assertFalse(new IpRule()->validate(null, $this->ctx()));
    }

    public function testJsonNonStringReturnsFalse(): void
    {
        $this->assertFalse(new JsonRule()->validate(42, $this->ctx()));
        $this->assertFalse(new JsonRule()->validate(null, $this->ctx()));
        $this->assertFalse(new JsonRule()->validate([], $this->ctx()));
    }

    public function testErrorCodes(): void
    {
        $this->assertSame('string.alpha', new AlphaRule()->getErrorCode());
        $this->assertSame('string.alphanumeric', new AlphanumericRule()->getErrorCode());
        $this->assertSame('string.uuid', new UuidRule()->getErrorCode());
        $this->assertSame('string.ulid', new UlidRule()->getErrorCode());
        $this->assertSame('string.ip', new IpRule()->getErrorCode());
        $this->assertSame('string.json', new JsonRule()->getErrorCode());
        $this->assertSame('string.hostname', new HostnameRule()->getErrorCode());
        $this->assertSame('string.slug', new SlugRule()->getErrorCode());
        $this->assertSame('string.starts_with', new StartsWithRule()->getErrorCode());
        $this->assertSame('string.ends_with', new EndsWithRule()->getErrorCode());
    }

    public function testDefaultMessages(): void
    {
        $this->assertSame('The {field} must contain only letters.', new AlphaRule()->getDefaultMessage());
        $this->assertSame('The {field} must contain only letters and numbers.', new AlphanumericRule()->getDefaultMessage());
        $this->assertSame('The {field} must be a valid UUID.', new UuidRule()->getDefaultMessage());
        $this->assertSame('The {field} must be a valid ULID.', new UlidRule()->getDefaultMessage());
        $this->assertSame('The {field} must be a valid IP address.', new IpRule()->getDefaultMessage());
        $this->assertSame('The {field} must be valid JSON.', new JsonRule()->getDefaultMessage());
        $this->assertSame('The {field} must be a valid hostname.', new HostnameRule()->getDefaultMessage());
        $this->assertSame('The {field} must be a valid URL slug.', new SlugRule()->getDefaultMessage());
        $this->assertSame('The {field} must start with {prefix}.', new StartsWithRule()->getDefaultMessage());
        $this->assertSame('The {field} must end with {suffix}.', new EndsWithRule()->getDefaultMessage());
    }
}
