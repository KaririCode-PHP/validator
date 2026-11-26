<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Integration;

use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Exception\ValidationException;
use KaririCode\Validator\Integration\ProcessorBridge;
use KaririCode\Validator\Provider\ValidatorServiceProvider;
use KaririCode\Validator\Rule\Logic\ConditionalRule;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests: end-to-end validation using the full component stack.
 */
final class FullValidationPipelineTest extends TestCase
{
    private ValidatorEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new ValidatorServiceProvider()->createEngine();
    }

    // ── User Registration Scenario ────────────────────────────────

    public function testValidUserRegistration(): void
    {
        $data = [
            'email' => 'walmir@kariricode.org',
            'name' => 'Walmir Silva',
            'age' => 30,
            'website' => 'https://kariricode.org',
        ];

        $rules = [
            'email' => ['required', 'email', ['length', ['max' => 255]]],
            'name' => ['required', ['length', ['min' => 2, 'max' => 100]]],
            'age' => ['required', 'positive', ['range', ['min' => 18, 'max' => 150]]],
            'website' => ['url'],
        ];

        $result = $this->engine->validate($data, $rules);

        $this->assertTrue($result->isValid());
        $this->assertSame(0, $result->errorCount());
    }

    public function testInvalidUserRegistrationCollectsAllErrors(): void
    {
        $data = [
            'email' => 'not-an-email',
            'name' => '',
            'age' => -5,
            'website' => 'invalid',
        ];

        $rules = [
            'email' => ['required', 'email'],
            'name' => ['required'],
            'age' => ['required', 'positive'],
            'website' => ['url'],
        ];

        $result = $this->engine->validate($data, $rules);

        $this->assertFalse($result->isValid());
        $this->assertGreaterThanOrEqual(4, $result->errorCount());
        $this->assertContains('email', $result->failedFields());
        $this->assertContains('name', $result->failedFields());
        $this->assertContains('age', $result->failedFields());
        $this->assertContains('website', $result->failedFields());
    }

    // ── Nested Data Scenario ──────────────────────────────────────

    public function testNestedAddressValidation(): void
    {
        $data = [
            'name' => 'Walmir',
            'address' => [
                'street' => 'Rua Principal',
                'city' => 'Juazeiro do Norte',
                'zip' => '63010-000',
            ],
        ];

        $rules = [
            'name' => ['required'],
            'address.street' => ['required', 'not_empty'],
            'address.city' => ['required', 'not_empty'],
            'address.zip' => ['required', ['pattern', ['pattern' => '/^\d{5}-\d{3}$/']]],
        ];

        $result = $this->engine->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    public function testNestedMissingFieldReportsCorrectPath(): void
    {
        $data = ['address' => ['street' => '']];

        $rules = [
            'address.street' => ['required'],
            'address.city' => ['required'],
        ];

        $result = $this->engine->validate($data, $rules);

        $this->assertFalse($result->isValid());
        $this->assertContains('address.street', $result->failedFields());
        $this->assertContains('address.city', $result->failedFields());
    }

    // ── ProcessorBridge Integration ───────────────────────────────

    public function testProcessorBridgeThrowsOnInvalidData(): void
    {
        $bridge = new ProcessorBridge(
            $this->engine,
            ['email' => ['required', 'email']],
        );

        try {
            $bridge->process(['email' => 'bad']);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            $this->assertSame(422, $e->getCode());
            $this->assertFalse($e->result->isValid());
        }
    }

    public function testProcessorBridgePassthroughValidData(): void
    {
        $bridge = new ProcessorBridge(
            $this->engine,
            ['email' => ['required', 'email']],
        );

        $output = $bridge->process(['email' => 'ok@test.com']);

        $this->assertTrue($output['result']->isValid());
        $this->assertSame('ok@test.com', $output['data']['email']);
    }

    // ── Merge Results Scenario ────────────────────────────────────

    public function testMergeResultsFromMultipleValidations(): void
    {
        $profileResult = $this->engine->validate(
            ['name' => ''],
            ['name' => ['required']],
        );

        $contactResult = $this->engine->validate(
            ['email' => 'bad'],
            ['email' => ['email']],
        );

        $merged = $profileResult->merge($contactResult);

        $this->assertFalse($merged->isValid());
        $this->assertSame(2, $merged->errorCount());
        $this->assertContains('name', $merged->failedFields());
        $this->assertContains('email', $merged->failedFields());
    }

    // ── Date + Type Rules Integration ─────────────────────────────

    public function testDateAndTypeRules(): void
    {
        $data = [
            'birth_date' => '1990-05-15',
            'tags' => ['php', 'architecture'],
            'score' => 42,
        ];

        $rules = [
            'birth_date' => ['required', 'date_format'],
            'tags' => [['count', ['min' => 1, 'max' => 10]]],
            'score' => ['integer', 'positive'],
        ];

        $result = $this->engine->validate($data, $rules);

        $this->assertTrue($result->isValid());
    }

    // ── Empty Data & Rules ────────────────────────────────────────

    public function testEmptyRulesAlwaysValid(): void
    {
        $result = $this->engine->validate(['any' => 'data'], []);

        $this->assertTrue($result->isValid());
    }

    public function testEmptyDataWithNoRequiredRules(): void
    {
        $result = $this->engine->validate(
            [],
            ['email' => ['email']],
        );

        // null value → email rule fails on null
        $this->assertFalse($result->isValid());
    }

    // ── ConditionalRule + CompositeRule Real-World Combo ───────────

    public function testConditionalBusinessValidation(): void
    {
        $cnpjRequired = new ConditionalRule(
            condition: fn ($v, $ctx): bool => ($ctx->getRootData()['type'] ?? '') === 'business',
            innerRule: new RequiredRule(),
        );

        $registry = new ValidatorServiceProvider()->createRegistry();
        $registry->register('cnpj_required', $cnpjRequired);
        $engine = new ValidatorEngine($registry);

        // Business without CNPJ → fails
        $result = $engine->validate(
            ['type' => 'business', 'cnpj' => ''],
            ['cnpj' => ['cnpj_required']],
        );
        $this->assertFalse($result->isValid());

        // Personal without CNPJ → passes (condition not met)
        $result = $engine->validate(
            ['type' => 'personal', 'cnpj' => ''],
            ['cnpj' => ['cnpj_required']],
        );
        $this->assertTrue($result->isValid());
    }

    // ── Error Message Interpolation ───────────────────────────────

    public function testErrorMessageContainsFieldName(): void
    {
        $result = $this->engine->validate(
            ['username' => ''],
            ['username' => ['required']],
        );

        $error = $result->getErrors()[0];
        $this->assertStringContainsString('username', $error->message);
        $this->assertStringNotContainsString('{field}', $error->message);
    }

    public function testErrorMessageInterpolatesParameters(): void
    {
        $result = $this->engine->validate(
            ['age' => -5],
            ['age' => [['range', ['min' => 0, 'max' => 150]]]],
        );

        $error = $result->getErrors()[0];
        $this->assertStringContainsString('0', $error->message);
        $this->assertStringContainsString('150', $error->message);
        $this->assertStringNotContainsString('{min}', $error->message);
        $this->assertStringNotContainsString('{max}', $error->message);
    }
}
