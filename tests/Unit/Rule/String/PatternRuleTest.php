<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\String;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\String\PatternRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PatternRule::class)]
final class PatternRuleTest extends TestCase
{
    private PatternRule $rule;

    protected function setUp(): void
    {
        $this->rule = new PatternRule();
    }

    public function testMatchesPattern(): void
    {
        $ctx = $this->ctx(['pattern' => '/^\d{5}-\d{3}$/']);
        $this->assertTrue($this->rule->validate('12345-678', $ctx));
    }

    public function testDoesNotMatchPattern(): void
    {
        $ctx = $this->ctx(['pattern' => '/^\d{5}-\d{3}$/']);
        $this->assertFalse($this->rule->validate('1234-56', $ctx));
    }

    public function testEmptyPatternPasses(): void
    {
        $ctx = $this->ctx(['pattern' => '']);
        $this->assertTrue($this->rule->validate('anything', $ctx));
    }

    public function testNoPatternParameterPasses(): void
    {
        $ctx = $this->ctx([]);
        $this->assertTrue($this->rule->validate('anything', $ctx));
    }

    public function testNonStringReturnsFalse(): void
    {
        $ctx = $this->ctx(['pattern' => '/\d+/']);
        $this->assertFalse($this->rule->validate(42, $ctx));
    }

    public function testPhonePattern(): void
    {
        $ctx = $this->ctx(['pattern' => '/^\+\d{2}\d{2}\d{8,9}$/']);
        $this->assertTrue($this->rule->validate('+5511999999999', $ctx));
        $this->assertFalse($this->rule->validate('11999999999', $ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('string.pattern', $this->rule->getErrorCode());
    }

    private function ctx(array $params): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('f')->withParameters($params);
    }
}
