<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Collection;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Collection\EachRule;
use KaririCode\Validator\Rule\Collection\KeyExistsRule;
use KaririCode\Validator\Rule\Collection\UniqueElementsRule;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use KaririCode\Validator\Rule\String\EmailRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EachRule::class)]
#[CoversClass(KeyExistsRule::class)]
#[CoversClass(UniqueElementsRule::class)]
final class NewCollectionRulesTest extends TestCase
{
    private function ctx(array $params = []): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('test')->withParameters($params);
    }

    public function testUniqueElementsWithUniqueValues(): void
    {
        $rule = new UniqueElementsRule();
        $this->assertTrue($rule->validate([1, 2, 3], $this->ctx()));
        $this->assertTrue($rule->validate(['a', 'b', 'c'], $this->ctx()));
    }

    public function testUniqueElementsWithDuplicates(): void
    {
        $rule = new UniqueElementsRule();
        $this->assertFalse($rule->validate([1, 2, 2, 3], $this->ctx()));
        $this->assertFalse($rule->validate(['a', 'a'], $this->ctx()));
    }

    public function testUniqueElementsRejectsNonArray(): void
    {
        $this->assertFalse(new UniqueElementsRule()->validate('string', $this->ctx()));
    }

    public function testEachRule(): void
    {
        $rule = new EachRule(new EmailRule());
        $this->assertTrue($rule->validate(['a@b.com', 'c@d.com'], $this->ctx()));
        $this->assertFalse($rule->validate(['a@b.com', 'invalid'], $this->ctx()));
        $this->assertFalse($rule->validate('not-array', $this->ctx()));
    }

    public function testEachEmptyArray(): void
    {
        $rule = new EachRule(new EmailRule());
        $this->assertTrue($rule->validate([], $this->ctx()));
    }

    public function testKeyExists(): void
    {
        $rule = new KeyExistsRule();
        $ctx = $this->ctx(['key' => 'name']);
        $this->assertTrue($rule->validate(['name' => 'Walmir'], $ctx));
        $this->assertFalse($rule->validate(['email' => 'a@b.com'], $ctx));
        $this->assertFalse($rule->validate('not-array', $ctx));
    }

    public function testKeyExistsMissingParam(): void
    {
        $this->assertFalse(new KeyExistsRule()->validate(['a' => 1], $this->ctx()));
    }

    public function testKeyExistsInvalidKeyType(): void
    {
        // Non-string, non-int key type from parameter should return false
        $ctx = $this->ctx(['key' => ['array', 'key']]);
        $this->assertFalse(new KeyExistsRule()->validate(['a' => 1], $ctx));
    }

    public function testErrorCodes(): void
    {
        $this->assertSame('collection.each', new EachRule(new RequiredRule())->getErrorCode());
        $this->assertSame('collection.key_exists', new KeyExistsRule()->getErrorCode());
        $this->assertSame('collection.unique', new UniqueElementsRule()->getErrorCode());
    }

    public function testDefaultMessages(): void
    {
        $this->assertSame('Each item in {field} must be valid.', new EachRule(new RequiredRule())->getDefaultMessage());
        $this->assertSame('The {field} must contain only unique values.', new UniqueElementsRule()->getDefaultMessage());
        $this->assertSame('The {field} must contain the key {key}.', new KeyExistsRule()->getDefaultMessage());
    }
}
