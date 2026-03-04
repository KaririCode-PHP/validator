<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Logic;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Logic\CompositeRule;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use KaririCode\Validator\Rule\String\EmailRule;
use KaririCode\Validator\Rule\String\LengthRule;
use PHPUnit\Framework\TestCase;

final class CompositeRuleTest extends TestCase
{
    public function testAllRulesPass(): void
    {
        $composite = new CompositeRule(
            'user.email_valid',
            'Invalid user email.',
            new RequiredRule(),
            new EmailRule(),
        );

        $ctx = ValidationContextImpl::create([])->withField('email');
        $this->assertTrue($composite->validate('user@example.com', $ctx));
    }

    public function testFailsWhenOneRuleFails(): void
    {
        $composite = new CompositeRule(
            'user.email_valid',
            'Invalid user email.',
            new RequiredRule(),
            new EmailRule(),
        );

        $ctx = ValidationContextImpl::create([])->withField('email');
        $this->assertFalse($composite->validate('not-an-email', $ctx));
    }

    public function testFailsOnFirstFailingRule(): void
    {
        $composite = new CompositeRule(
            'user.email_valid',
            'Invalid user email.',
            new RequiredRule(),
            new EmailRule(),
        );

        $ctx = ValidationContextImpl::create([])->withField('email');
        $this->assertFalse($composite->validate(null, $ctx));
    }

    public function testEmptyCompositeAlwaysPasses(): void
    {
        $composite = new CompositeRule('empty', 'No rules.');

        $ctx = ValidationContextImpl::create([])->withField('field');
        $this->assertTrue($composite->validate('anything', $ctx));
    }

    public function testReturnsCustomErrorCodeAndMessage(): void
    {
        $composite = new CompositeRule('custom.code', 'Custom message.');

        $this->assertSame('custom.code', $composite->getErrorCode());
        $this->assertSame('Custom message.', $composite->getDefaultMessage());
    }

    public function testContextParametersPropagateToInnerRules(): void
    {
        $composite = new CompositeRule(
            'field.valid',
            'Validation failed.',
            new RequiredRule(),
            new LengthRule(),
        );

        $ctx = ValidationContextImpl::create([])
            ->withField('name')
            ->withParameters(['min' => 3, 'max' => 50]);

        $this->assertTrue($composite->validate('Walmir', $ctx));
        $this->assertFalse($composite->validate('ab', $ctx));
    }
}
