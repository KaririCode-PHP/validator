<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Provider;

use KaririCode\Validator\Contract\RuleRegistry;
use KaririCode\Validator\Core\AttributeValidator;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Provider\ValidatorServiceProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValidatorServiceProviderTest extends TestCase
{
    private ValidatorServiceProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new ValidatorServiceProvider();
    }

    public function testCreateRegistryReturnsRuleRegistry(): void
    {
        $registry = $this->provider->createRegistry();

        $this->assertInstanceOf(RuleRegistry::class, $registry);
    }

    /**
     * @return iterable<string[]>
     */
    public static function builtInAliasProvider(): iterable
    {
        // Logic (4 stateless)
        yield ['required'];
        yield ['choice'];
        yield ['not_null'];
        yield ['nullable'];
        // String (15)
        yield ['email'];
        yield ['url'];
        yield ['length'];
        yield ['pattern'];
        yield ['not_empty'];
        yield ['alpha'];
        yield ['alphanumeric'];
        yield ['uuid'];
        yield ['ulid'];
        yield ['ip'];
        yield ['json'];
        yield ['hostname'];
        yield ['slug'];
        yield ['starts_with'];
        yield ['ends_with'];
        // Numeric (6)
        yield ['range'];
        yield ['positive'];
        yield ['integer'];
        yield ['negative'];
        yield ['negative_or_zero'];
        yield ['decimal'];
        // Comparison (7)
        yield ['equal_to'];
        yield ['not_equal_to'];
        yield ['greater_than'];
        yield ['greater_than_or_equal'];
        yield ['less_than'];
        yield ['less_than_or_equal'];
        yield ['between'];
        // Date (4)
        yield ['date_format'];
        yield ['date_before'];
        yield ['date_after'];
        yield ['date_between'];
        // Type (2)
        yield ['type'];
        yield ['instance_of'];
        // Collection (3)
        yield ['count'];
        yield ['unique'];
        yield ['key_exists'];
        // CrossField (4)
        yield ['same_as'];
        yield ['different_from'];
        yield ['required_with'];
        yield ['required_without'];
        // Financial (3)
        yield ['luhn'];
        yield ['iban'];
        yield ['bic'];
        // Brazilian (4)
        yield ['cpf'];
        yield ['cnpj'];
        yield ['cep'];
        yield ['pis'];
    }

    #[DataProvider('builtInAliasProvider')]
    public function testAllBuiltInAliasesRegistered(string $alias): void
    {
        $registry = $this->provider->createRegistry();

        $this->assertTrue($registry->has($alias), "Alias '{$alias}' not registered.");
    }

    public function testRegistryHasExactly50Aliases(): void
    {
        $registry = $this->provider->createRegistry();

        $aliases = $registry->aliases();
        $this->assertCount(52, $aliases, 'Expected 52 built-in aliases. Got: ' . implode(', ', $aliases));
    }

    public function testCreateEngineReturnsValidatorEngine(): void
    {
        $engine = $this->provider->createEngine();

        $this->assertInstanceOf(ValidatorEngine::class, $engine);
    }

    public function testCreatedEngineCanValidate(): void
    {
        $engine = $this->provider->createEngine();

        $result = $engine->validate(
            ['email' => 'user@example.com'],
            ['email' => ['required', 'email']],
        );

        $this->assertTrue($result->isValid());
    }

    public function testCreateAttributeValidatorReturnsInstance(): void
    {
        $validator = $this->provider->createAttributeValidator();

        $this->assertInstanceOf(AttributeValidator::class, $validator);
    }
}
