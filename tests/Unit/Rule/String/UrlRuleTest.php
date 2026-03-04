<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\String;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\String\UrlRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UrlRuleTest extends TestCase
{
    private UrlRule $rule;
    private ValidationContextImpl $ctx;

    protected function setUp(): void
    {
        $this->rule = new UrlRule();
        $this->ctx = ValidationContextImpl::create([])->withField('url');
    }

    #[DataProvider('validUrlProvider')]
    public function testValidUrls(string $url): void
    {
        $this->assertTrue($this->rule->validate($url, $this->ctx));
    }

    public static function validUrlProvider(): iterable
    {
        yield 'https' => ['https://example.com'];
        yield 'http' => ['http://example.com'];
        yield 'with path' => ['https://example.com/path/to/page'];
        yield 'with query' => ['https://example.com?key=value'];
        yield 'ftp' => ['ftp://files.example.com'];
    }

    #[DataProvider('invalidUrlProvider')]
    public function testInvalidUrls(mixed $url): void
    {
        $this->assertFalse($this->rule->validate($url, $this->ctx));
    }

    public static function invalidUrlProvider(): iterable
    {
        yield 'no scheme' => ['example.com'];
        yield 'spaces' => ['https://example .com'];
        yield 'empty' => [''];
        yield 'null' => [null];
        yield 'integer' => [42];
    }

    public function testErrorCode(): void
    {
        $this->assertSame('string.url', $this->rule->getErrorCode());
    }
}
