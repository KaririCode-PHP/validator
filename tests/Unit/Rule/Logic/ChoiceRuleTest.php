<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Logic;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Logic\ChoiceRule;
use PHPUnit\Framework\TestCase;

final class ChoiceRuleTest extends TestCase
{
    private ChoiceRule $rule;

    protected function setUp(): void
    {
        $this->rule = new ChoiceRule();
    }

    public function testValidChoice(): void
    {
        $ctx = ValidationContextImpl::create([])
            ->withField('status')
            ->withParameters(['choices' => ['active', 'inactive', 'pending']]);

        $this->assertTrue($this->rule->validate('active', $ctx));
    }

    public function testInvalidChoice(): void
    {
        $ctx = ValidationContextImpl::create([])
            ->withField('status')
            ->withParameters(['choices' => ['active', 'inactive']]);

        $this->assertFalse($this->rule->validate('deleted', $ctx));
    }

    public function testStrictComparison(): void
    {
        $ctx = ValidationContextImpl::create([])
            ->withField('value')
            ->withParameters(['choices' => [1, 2, 3]]);

        $this->assertFalse($this->rule->validate('1', $ctx));
        $this->assertTrue($this->rule->validate(1, $ctx));
    }

    public function testEmptyChoicesPassesValidation(): void
    {
        $ctx = ValidationContextImpl::create([])
            ->withField('field')
            ->withParameters(['choices' => []]);

        $this->assertTrue($this->rule->validate('anything', $ctx));
    }

    public function testNoChoicesParameterPassesValidation(): void
    {
        $ctx = ValidationContextImpl::create([])->withField('field');

        $this->assertTrue($this->rule->validate('anything', $ctx));
    }

    public function testErrorCode(): void
    {
        $this->assertSame('logic.choice', $this->rule->getErrorCode());
    }

    public function testNullValueNotInChoices(): void
    {
        $ctx = ValidationContextImpl::create([])
            ->withField('status')
            ->withParameters(['choices' => ['active', 'inactive']]);

        $this->assertFalse($this->rule->validate(null, $ctx));
    }

    public function testNullValueInChoicesIfExplicit(): void
    {
        $ctx = ValidationContextImpl::create([])
            ->withField('field')
            ->withParameters(['choices' => [null, 'a', 'b']]);

        $this->assertTrue($this->rule->validate(null, $ctx));
    }
}
