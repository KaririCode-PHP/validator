<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use KaririCode\Validator\Contract\MessageResolver;
use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\InMemoryRuleRegistry;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use KaririCode\Validator\Rule\String\EmailRule;
use KaririCode\Validator\Rule\String\LengthRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidatorEngine::class)]
final class ValidatorEngineTest extends TestCase
{
    private InMemoryRuleRegistry $registry;
    private ValidatorEngine $engine;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRuleRegistry();
        $this->registry->register('required', new RequiredRule());
        $this->registry->register('email', new EmailRule());
        $this->registry->register('length', new LengthRule());

        $this->engine = new ValidatorEngine($this->registry);
    }

    // ── Basic Validation ──────────────────────────────────────────

    public function testValidDataReturnsValidResult(): void
    {
        $result = $this->engine->validate(
            ['email' => 'user@example.com'],
            ['email' => ['required', 'email']],
        );

        $this->assertTrue($result->isValid());
        $this->assertSame(0, $result->errorCount());
    }

    public function testInvalidDataCollectsErrors(): void
    {
        $result = $this->engine->validate(
            ['email' => 'not-an-email'],
            ['email' => ['required', 'email']],
        );

        $this->assertFalse($result->isValid());
        $this->assertSame(1, $result->errorCount());
        $this->assertSame('string.email', $result->getErrors()[0]->errorCode);
    }

    public function testMultipleFieldsMultipleErrors(): void
    {
        $result = $this->engine->validate(
            ['email' => '', 'name' => ''],
            [
                'email' => ['required', 'email'],
                'name' => ['required'],
            ],
        );

        $this->assertFalse($result->isValid());
        $this->assertCount(2, $result->failedFields());
    }

    // ── Rule Definition Formats ───────────────────────────────────

    public function testStringAliasFormat(): void
    {
        $result = $this->engine->validate(
            ['email' => ''],
            ['email' => ['required']],
        );

        $this->assertFalse($result->isValid());
    }

    public function testArrayAliasWithParameters(): void
    {
        $result = $this->engine->validate(
            ['name' => 'ab'],
            ['name' => [['length', ['min' => 3, 'max' => 50]]]],
        );

        $this->assertFalse($result->isValid());
        $this->assertSame('string.length', $result->getErrors()[0]->errorCode);
    }

    public function testDirectRuleInstance(): void
    {
        $result = $this->engine->validate(
            ['email' => 'bad'],
            ['email' => [new EmailRule()]],
        );

        $this->assertFalse($result->isValid());
    }

    public function testMixedRuleFormats(): void
    {
        $result = $this->engine->validate(
            ['email' => 'user@example.com'],
            ['email' => [
                'required',
                new EmailRule(),
                ['length', ['max' => 255]],
            ]],
        );

        $this->assertTrue($result->isValid());
    }

    // ── Dot-Notation ──────────────────────────────────────────────

    public function testDotNotationResolvesNestedValues(): void
    {
        $result = $this->engine->validate(
            ['address' => ['city' => '']],
            ['address.city' => ['required']],
        );

        $this->assertFalse($result->isValid());
        $this->assertSame('address.city', $result->getErrors()[0]->field);
    }

    public function testDotNotationDeepNesting(): void
    {
        $result = $this->engine->validate(
            ['level1' => ['level2' => ['level3' => 'value']]],
            ['level1.level2.level3' => ['required']],
        );

        $this->assertTrue($result->isValid());
    }

    public function testDotNotationMissingKeyResolvesToNull(): void
    {
        $result = $this->engine->validate(
            ['address' => []],
            ['address.zip' => ['required']],
        );

        $this->assertFalse($result->isValid());
    }

    // ── Edge Cases ────────────────────────────────────────────────

    public function testEmptyFieldRulesReturnsValid(): void
    {
        $result = $this->engine->validate(['email' => 'test'], []);

        $this->assertTrue($result->isValid());
    }

    public function testEmptyDataWithRequiredField(): void
    {
        $result = $this->engine->validate(
            [],
            ['email' => ['required']],
        );

        $this->assertFalse($result->isValid());
    }

    public function testMultipleRulesPerFieldCollectAllErrors(): void
    {
        $result = $this->engine->validate(
            ['email' => ''],
            ['email' => ['required', 'email']],
        );

        $emailErrors = $result->errorsFor('email');
        // '' fails required (trimmed empty) AND email (invalid format)
        $this->assertCount(2, $emailErrors);
        $this->assertSame('logic.required', $emailErrors[0]->errorCode);
        $this->assertSame('string.email', $emailErrors[1]->errorCode);
    }

    // ── Message Interpolation ─────────────────────────────────────

    public function testDefaultMessageInterpolation(): void
    {
        $result = $this->engine->validate(
            ['name' => 'ab'],
            ['name' => [['length', ['min' => 3]]]],
        );

        $message = $result->getErrors()[0]->message;
        $this->assertStringContainsString('name', $message);
    }

    // ── Custom MessageResolver ────────────────────────────────────

    public function testCustomMessageResolver(): void
    {
        $resolver = new class implements MessageResolver {
            public function resolve(string $errorCode, array $parameters = [], ?string $locale = null): string
            {
                return "Custom: {$errorCode}";
            }
        };

        $engine = new ValidatorEngine($this->registry, $resolver);

        $result = $engine->validate(
            ['email' => 'bad'],
            ['email' => ['email']],
        );

        $this->assertSame('Custom: string.email', $result->getErrors()[0]->message);
    }

    // ── Validation Group Passthrough ──────────────────────────────

    public function testGroupPassedToContext(): void
    {
        $capturedGroup = null;
        $capturingRule = new class($capturedGroup) implements ValidationRule {
            private mixed $ref;

            public function __construct(mixed &$ref)
            {
                $this->ref = &$ref;
            }

            public function validate(mixed $value, ValidationContext $context): bool
            {
                $this->ref = $context->getGroup();
                return true;
            }

            public function getErrorCode(): string
            {
                return 'test';
            }

            public function getDefaultMessage(): string
            {
                return 'Test.';
            }
        };

        $this->registry->register('capture_group', $capturingRule);

        $this->engine->validate(
            ['f' => 'v'],
            ['f' => ['capture_group']],
            'update',
        );

        $this->assertSame('update', $capturedGroup);
    }

    // ── Error Handling ────────────────────────────────────────────

    public function testUnknownAliasThrowsInvalidRuleException(): void
    {
        $this->expectException(\KaririCode\Validator\Exception\InvalidRuleException::class);

        $this->engine->validate(
            ['f' => 'v'],
            ['f' => ['nonexistent_alias']],
        );
    }

    // ── Error Context Passthrough ─────────────────────────────────

    public function testParametersPassedAsErrorContext(): void
    {
        $result = $this->engine->validate(
            ['name' => 'ab'],
            ['name' => [['length', ['min' => 3, 'max' => 50]]]],
        );

        $error = $result->getErrors()[0];
        $this->assertSame('string.length', $error->errorCode);
        $this->assertSame(3, $error->context['min']);
        $this->assertSame(50, $error->context['max']);
    }

    // ── Null Group Default ────────────────────────────────────────

    public function testDefaultGroupIsNull(): void
    {
        $capturedGroup = 'sentinel';
        $capturingRule = new class($capturedGroup) implements ValidationRule {
            private mixed $ref;

            public function __construct(mixed &$ref)
            {
                $this->ref = &$ref;
            }

            public function validate(mixed $value, ValidationContext $context): bool
            {
                $this->ref = $context->getGroup();
                return true;
            }

            public function getErrorCode(): string
            {
                return 'test';
            }

            public function getDefaultMessage(): string
            {
                return 'Test.';
            }
        };

        $this->registry->register('capture_null', $capturingRule);

        $this->engine->validate(
            ['f' => 'v'],
            ['f' => ['capture_null']],
        );

        $this->assertNull($capturedGroup);
    }
}
