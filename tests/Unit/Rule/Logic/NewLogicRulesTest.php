<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Logic;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Logic\AtLeastOneOfRule;
use KaririCode\Validator\Rule\Logic\CallbackRule;
use KaririCode\Validator\Rule\Logic\NotNullRule;
use KaririCode\Validator\Rule\Logic\NullableRule;
use KaririCode\Validator\Rule\String\EmailRule;
use KaririCode\Validator\Rule\String\UrlRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullableRule::class)]
#[CoversClass(NotNullRule::class)]
#[CoversClass(CallbackRule::class)]
#[CoversClass(AtLeastOneOfRule::class)]
final class NewLogicRulesTest extends TestCase
{
    private function ctx(): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('test');
    }

    // ── NullableRule ──
    public function testNullableAcceptsNull(): void
    {
        $this->assertTrue(new NullableRule()->validate(null, $this->ctx()));
    }

    public function testNullableRejectsNonNull(): void
    {
        $this->assertFalse(new NullableRule()->validate('hello', $this->ctx()));
    }

    // ── NotNullRule ──
    public function testNotNullAcceptsValue(): void
    {
        $this->assertTrue(new NotNullRule()->validate('hello', $this->ctx()));
        $this->assertTrue(new NotNullRule()->validate(0, $this->ctx()));
        $this->assertTrue(new NotNullRule()->validate('', $this->ctx()));
        $this->assertTrue(new NotNullRule()->validate(false, $this->ctx()));
    }

    public function testNotNullRejectsNull(): void
    {
        $this->assertFalse(new NotNullRule()->validate(null, $this->ctx()));
    }

    // ── CallbackRule ──
    public function testCallbackWithPassingCondition(): void
    {
        $rule = new CallbackRule(fn ($v): bool => $v % 2 === 0, 'logic.even', 'Must be even');
        $this->assertTrue($rule->validate(42, $this->ctx()));
        $this->assertFalse($rule->validate(41, $this->ctx()));
        $this->assertSame('logic.even', $rule->getErrorCode());
    }

    public function testCallbackWithContextAccess(): void
    {
        $rule = new CallbackRule(
            fn ($v, $ctx): bool => $v === $ctx->getParameter('expected'),
        );
        $ctx = $this->ctx()->withParameters(['expected' => 'yes']);
        $this->assertTrue($rule->validate('yes', $ctx));
        $this->assertFalse($rule->validate('no', $ctx));
    }

    // ── AtLeastOneOfRule ──
    public function testAtLeastOneOfPassesWhenOneRuleMatches(): void
    {
        $rule = new AtLeastOneOfRule(new EmailRule(), new UrlRule());
        $this->assertTrue($rule->validate('user@test.com', $this->ctx()));
        $this->assertTrue($rule->validate('https://example.com', $this->ctx()));
    }

    public function testAtLeastOneOfFailsWhenNoneMatch(): void
    {
        $rule = new AtLeastOneOfRule(new EmailRule(), new UrlRule());
        $this->assertFalse($rule->validate('neither', $this->ctx()));
    }

    // ── Error codes ──
    public function testErrorCodes(): void
    {
        $this->assertSame('logic.nullable', new NullableRule()->getErrorCode());
        $this->assertSame('logic.not_null', new NotNullRule()->getErrorCode());
        $this->assertSame('logic.callback', new CallbackRule(fn (): true => true)->getErrorCode());
        $this->assertSame('logic.at_least_one_of', new AtLeastOneOfRule()->getErrorCode());
    }

    public function testDefaultMessages(): void
    {
        $this->assertSame('The {field} must be null or valid.', new NullableRule()->getDefaultMessage());
        $this->assertSame('The {field} must not be null.', new NotNullRule()->getDefaultMessage());
        $this->assertSame('The {field} must pass at least one validation rule.', new AtLeastOneOfRule()->getDefaultMessage());
        $this->assertSame('The {field} is invalid.', new CallbackRule(fn (): true => true)->getDefaultMessage());
    }
}
