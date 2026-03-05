<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Logic;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Logic\ConditionalRule;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConditionalRule::class)]
final class ConditionalRuleTest extends TestCase
{
    public function testAppliesRuleWhenConditionMet(): void
    {
        $rule = new ConditionalRule(
            condition: fn ($v, $ctx): bool => $ctx->getRootData()['type'] === 'business',
            innerRule: new RequiredRule(),
        );

        $ctx = ValidationContextImpl::create(['type' => 'business'])->withField('cnpj');

        $this->assertFalse($rule->validate(null, $ctx));
    }

    public function testSkipsRuleWhenConditionNotMet(): void
    {
        $rule = new ConditionalRule(
            condition: fn ($v, $ctx): bool => $ctx->getRootData()['type'] === 'business',
            innerRule: new RequiredRule(),
        );

        $ctx = ValidationContextImpl::create(['type' => 'personal'])->withField('cnpj');

        $this->assertTrue($rule->validate(null, $ctx));
    }

    public function testDelegatesErrorCodeToInnerRule(): void
    {
        $rule = new ConditionalRule(
            condition: fn (): true => true,
            innerRule: new RequiredRule(),
        );

        $this->assertSame('logic.required', $rule->getErrorCode());
    }

    public function testDelegatesMessageToInnerRule(): void
    {
        $rule = new ConditionalRule(
            condition: fn (): true => true,
            innerRule: new RequiredRule(),
        );

        $this->assertStringContainsString('{field}', $rule->getDefaultMessage());
    }

    public function testConditionMetAndInnerPasses(): void
    {
        $rule = new ConditionalRule(
            condition: fn ($v, $ctx): bool => $ctx->getRootData()['type'] === 'business',
            innerRule: new RequiredRule(),
        );

        $ctx = ValidationContextImpl::create(['type' => 'business', 'cnpj' => '12345678000100'])->withField('cnpj');

        $this->assertTrue($rule->validate('12345678000100', $ctx));
    }
}
