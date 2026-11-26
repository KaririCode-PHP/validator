<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Type;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Type\InstanceOfRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InstanceOfRule::class)]
final class InstanceOfRuleTest extends TestCase
{
    private InstanceOfRule $rule;

    protected function setUp(): void
    {
        $this->rule = new InstanceOfRule();
    }

    public function testMatchesExactClass(): void
    {
        $ctx = $this->ctx(\stdClass::class);
        $this->assertTrue($this->rule->validate(new \stdClass(), $ctx));
    }

    public function testMatchesInterface(): void
    {
        $ctx = $this->ctx(\Countable::class);
        $this->assertTrue($this->rule->validate(new \ArrayObject(), $ctx));
    }

    public function testDoesNotMatchDifferentClass(): void
    {
        $ctx = $this->ctx(\ArrayObject::class);
        $this->assertFalse($this->rule->validate(new \stdClass(), $ctx));
    }

    public function testNonObjectReturnsFalse(): void
    {
        $ctx = $this->ctx(\stdClass::class);
        $this->assertFalse($this->rule->validate('string', $ctx));
        $this->assertFalse($this->rule->validate(null, $ctx));
        $this->assertFalse($this->rule->validate(42, $ctx));
    }

    public function testEmptyClassNameReturnsFalse(): void
    {
        $ctx = $this->ctx('');
        $this->assertFalse($this->rule->validate(new \stdClass(), $ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('type.instance_of', $this->rule->getErrorCode());
    }

    public function testDefaultMessage(): void
    {
        $this->assertSame('The {field} must be an instance of {class}.', $this->rule->getDefaultMessage());
    }

    private function ctx(string $class): ValidationContextImpl
    {
        return ValidationContextImpl::create([])
            ->withField('f')
            ->withParameters(['class' => $class]);
    }
}
