<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Exception;

use KaririCode\Validator\Exception\InvalidRuleException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidRuleException::class)]
final class InvalidRuleExceptionTest extends TestCase
{
    public function testDuplicateAlias(): void
    {
        $e = InvalidRuleException::duplicateAlias('email');

        $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        $this->assertStringContainsString("'email'", $e->getMessage());
        $this->assertStringContainsString('already registered', $e->getMessage());
    }

    public function testUnknownAlias(): void
    {
        $e = InvalidRuleException::unknownAlias('foobar');

        $this->assertStringContainsString("'foobar'", $e->getMessage());
    }

    public function testInvalidConfiguration(): void
    {
        $e = InvalidRuleException::invalidConfiguration('length', 'min must be >= 0');

        $this->assertStringContainsString("'length'", $e->getMessage());
        $this->assertStringContainsString('min must be >= 0', $e->getMessage());
    }
}
