<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\String;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\String\EmailRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailRule::class)]
final class EmailRuleTest extends TestCase
{
    private EmailRule $rule;
    private ValidationContextImpl $ctx;

    protected function setUp(): void
    {
        $this->rule = new EmailRule();
        $this->ctx = ValidationContextImpl::create([])->withField('email');
    }

    #[DataProvider('validEmailProvider')]
    public function testValidEmails(string $email): void
    {
        $this->assertTrue($this->rule->validate($email, $this->ctx));
    }

    public static function validEmailProvider(): iterable
    {
        yield 'simple' => ['user@example.com'];
        yield 'with subdomain' => ['user@mail.example.com'];
        yield 'with plus' => ['user+tag@example.com'];
        yield 'with dots' => ['first.last@example.com'];
        yield 'numeric local' => ['123@example.com'];
        yield 'hyphen domain' => ['user@my-domain.com'];
    }

    #[DataProvider('invalidEmailProvider')]
    public function testInvalidEmails(mixed $email): void
    {
        $this->assertFalse($this->rule->validate($email, $this->ctx));
    }

    public static function invalidEmailProvider(): iterable
    {
        yield 'no at' => ['userexample.com'];
        yield 'no domain' => ['user@'];
        yield 'no local' => ['@example.com'];
        yield 'spaces' => ['user @example.com'];
        yield 'double at' => ['user@@example.com'];
        yield 'null' => [null];
        yield 'integer' => [42];
        yield 'empty string' => [''];
        yield 'boolean' => [true];
        yield 'array' => [['user@example.com']];
    }

    public function testErrorCode(): void
    {
        $this->assertSame('string.email', $this->rule->getErrorCode());
    }
}
