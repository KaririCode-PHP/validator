<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Integration;

use KaririCode\Validator\Provider\ValidatorServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration: verifies all 52 built-in rules register without conflict
 * and the engine resolves each alias correctly.
 */
final class FullRuleRegistrationTest extends TestCase
{
    public function testAll52RulesRegistered(): void
    {
        $provider = new ValidatorServiceProvider();
        $registry = $provider->createRegistry();

        $expected = [
            // Logic (4 stateless)
            'required', 'choice', 'not_null', 'nullable',
            // String (15)
            'email', 'url', 'length', 'pattern', 'not_empty',
            'alpha', 'alphanumeric', 'uuid', 'ulid', 'ip',
            'json', 'hostname', 'slug', 'starts_with', 'ends_with',
            // Numeric (6)
            'range', 'positive', 'integer', 'negative', 'negative_or_zero', 'decimal',
            // Comparison (7)
            'equal_to', 'not_equal_to', 'greater_than', 'greater_than_or_equal',
            'less_than', 'less_than_or_equal', 'between',
            // Date (4)
            'date_format', 'date_before', 'date_after', 'date_between',
            // Type (2)
            'type', 'instance_of',
            // Collection (3)
            'count', 'unique', 'key_exists',
            // CrossField (4)
            'same_as', 'different_from', 'required_with', 'required_without',
            // Financial (3)
            'luhn', 'iban', 'bic',
            // Brazilian (4)
            'cpf', 'cnpj', 'cep', 'pis',
        ];

        foreach ($expected as $alias) {
            $this->assertTrue($registry->has($alias), "Missing alias: {$alias}");
        }

        $this->assertCount(count($expected), $registry->aliases());
    }

    public function testEngineValidatesWithAllRuleCategories(): void
    {
        $engine = (new ValidatorServiceProvider())->createEngine();

        $result = $engine->validate([
            'name' => 'Walmir',
            'email' => 'walmir@kariricode.org',
            'age' => 30,
            'cpf' => '529.982.247-25',
            'date' => '2025-06-15',
        ], [
            'name' => ['required', ['length', ['min' => 2, 'max' => 100]]],
            'email' => ['required', 'email'],
            'age' => ['required', 'positive', ['range', ['min' => 18, 'max' => 150]]],
            'cpf' => ['required', 'cpf'],
            'date' => ['required', ['date_format', ['format' => 'Y-m-d']]],
        ]);

        $this->assertTrue($result->isValid());
    }

    public function testCrossFieldValidation(): void
    {
        $engine = (new ValidatorServiceProvider())->createEngine();

        $result = $engine->validate([
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ], [
            'password_confirmation' => [['same_as', ['other' => 'password']]],
        ]);

        $this->assertTrue($result->isValid());

        // Mismatch
        $result = $engine->validate([
            'password' => 'secret123',
            'password_confirmation' => 'different',
        ], [
            'password_confirmation' => [['same_as', ['other' => 'password']]],
        ]);

        $this->assertFalse($result->isValid());
    }

    public function testFinancialValidation(): void
    {
        $engine = (new ValidatorServiceProvider())->createEngine();

        $result = $engine->validate([
            'card' => '4532015112830366',
            'iban' => 'GB29NWBK60161331926819',
            'bic' => 'NWBKGB2L',
        ], [
            'card' => ['luhn'],
            'iban' => ['iban'],
            'bic' => ['bic'],
        ]);

        $this->assertTrue($result->isValid());
    }

    public function testBrazilianValidation(): void
    {
        $engine = (new ValidatorServiceProvider())->createEngine();

        $result = $engine->validate([
            'cpf' => '529.982.247-25',
            'cnpj' => '11.222.333/0001-81',
            'cep' => '63100-000',
            'pis' => '12345678900',
        ], [
            'cpf' => ['cpf'],
            'cnpj' => ['cnpj'],
            'cep' => ['cep'],
            'pis' => ['pis'],
        ]);

        $this->assertTrue($result->isValid());
    }

    public function testComparisonRulesIntegration(): void
    {
        $engine = (new ValidatorServiceProvider())->createEngine();

        $result = $engine->validate(
            ['score' => 85],
            ['score' => [
                ['greater_than', ['threshold' => 0]],
                ['less_than_or_equal', ['threshold' => 100]],
            ]],
        );

        $this->assertTrue($result->isValid());
    }

    public function testDateRangeIntegration(): void
    {
        $engine = (new ValidatorServiceProvider())->createEngine();

        $result = $engine->validate(
            ['event_date' => '2025-06-15'],
            ['event_date' => [
                ['date_format', ['format' => 'Y-m-d']],
                ['date_after', ['after' => '2025-01-01', 'format' => 'Y-m-d']],
                ['date_before', ['before' => '2025-12-31', 'format' => 'Y-m-d']],
            ]],
        );

        $this->assertTrue($result->isValid());
    }
}
