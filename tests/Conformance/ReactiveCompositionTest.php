<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Conformance;

use KaririCode\Validator\Provider\ValidatorServiceProvider;
use KaririCode\Validator\Result\ValidationResult;
use KaririCode\Validator\Rule\Logic\CompositeRule;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use KaririCode\Validator\Rule\String\EmailRule;
use KaririCode\Validator\Rule\String\LengthRule;
use PHPUnit\Framework\TestCase;

/**
 * ARFA 1.3 P2 — Reactive Flow Composition.
 *
 * Flow = f₁ ∘ f₂ ∘ ... ∘ fₙ where each fᵢ: State → Promise<State>
 */
final class ReactiveCompositionTest extends TestCase
{
    public function testRulesComposeViaEnginePipeline(): void
    {
        $engine = (new ValidatorServiceProvider())->createEngine();

        $result = $engine->validate(
            ['email' => 'user@example.com'],
            ['email' => ['required', 'email', ['length', ['max' => 255]]]],
        );

        $this->assertTrue($result->isValid());
    }

    public function testCompositeRuleComposesMultipleRules(): void
    {
        $composite = new CompositeRule(
            'user.email_valid',
            'Invalid user email.',
            new RequiredRule(),
            new EmailRule(),
            new LengthRule(),
        );

        $ctx = \KaririCode\Validator\Core\ValidationContextImpl::create([])
            ->withField('email')
            ->withParameters(['max' => 255]);

        $this->assertTrue($composite->validate('user@example.com', $ctx));
        $this->assertFalse($composite->validate('', $ctx));
    }

    public function testResultMergeComposesMultipleValidations(): void
    {
        $engine = (new ValidatorServiceProvider())->createEngine();

        $profileResult = $engine->validate(
            ['name' => ''],
            ['name' => ['required']],
        );

        $contactResult = $engine->validate(
            ['email' => 'bad'],
            ['email' => ['email']],
        );

        $merged = $profileResult->merge($contactResult);

        $this->assertSame(2, $merged->errorCount());
    }
}
