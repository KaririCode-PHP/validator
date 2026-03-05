<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\InMemoryRuleRegistry;
use KaririCode\Validator\Exception\InvalidRuleException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryRuleRegistry::class)]
final class InMemoryRuleRegistryTest extends TestCase
{
    private InMemoryRuleRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRuleRegistry();
    }

    public function testRegisterAndResolve(): void
    {
        $rule = $this->createStubRule();
        $this->registry->register('my_rule', $rule);

        $this->assertSame($rule, $this->registry->resolve('my_rule'));
    }

    public function testHasReturnsTrueForRegistered(): void
    {
        $this->registry->register('email', $this->createStubRule());

        $this->assertTrue($this->registry->has('email'));
    }

    public function testHasReturnsFalseForUnregistered(): void
    {
        $this->assertFalse($this->registry->has('nonexistent'));
    }

    public function testAliasesReturnsAllRegisteredNames(): void
    {
        $this->registry->register('email', $this->createStubRule());
        $this->registry->register('required', $this->createStubRule());

        $aliases = $this->registry->aliases();
        $this->assertCount(2, $aliases);
        $this->assertContains('email', $aliases);
        $this->assertContains('required', $aliases);
    }

    public function testAliasesReturnsEmptyWhenNoneRegistered(): void
    {
        $this->assertSame([], $this->registry->aliases());
    }

    public function testAliasesReturnsSequentialList(): void
    {
        $this->registry->register('email', $this->createStubRule());
        $this->registry->register('required', $this->createStubRule());

        $aliases = $this->registry->aliases();

        // Must be a flat sequential array (not associative keys)
        $this->assertSame(array_values($aliases), $aliases);
    }

    public function testRegisterDuplicateThrows(): void
    {
        $this->registry->register('email', $this->createStubRule());

        $this->expectException(InvalidRuleException::class);
        $this->expectExceptionMessage("'email' is already registered");

        $this->registry->register('email', $this->createStubRule());
    }

    public function testResolveUnknownThrows(): void
    {
        $this->expectException(InvalidRuleException::class);
        $this->expectExceptionMessage("'unknown'");

        $this->registry->resolve('unknown');
    }

    private function createStubRule(): ValidationRule
    {
        return new class implements ValidationRule {
            public function validate(mixed $value, ValidationContext $context): bool
            {
                return true;
            }

            public function getErrorCode(): string
            {
                return 'stub.rule';
            }

            public function getDefaultMessage(): string
            {
                return 'Stub.';
            }
        };
    }
}
