<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Integration;

use ArrayObject;
use KaririCode\Contract\Processor\Processor;
use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Core\AttributeValidator;
use KaririCode\Validator\Core\InMemoryRuleRegistry;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Event\ValidationCompletedEvent;
use KaririCode\Validator\Exception\ValidationException;
use KaririCode\Validator\Integration\ValidationProcessor;
use KaririCode\Validator\Rule\Brazilian\CnpjRule;
use KaririCode\Validator\Rule\Brazilian\CpfRule;
use KaririCode\Validator\Rule\Logic\AtLeastOneOfRule;
use KaririCode\Validator\Rule\Logic\ConditionalRule;
use KaririCode\Validator\Tests\Support\ReflectionInspector;
use KaririCode\Validator\Tests\Support\SignUp;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

// ── Integration: RegistrationFlowTest ────────────────────────────────
// A company sign-up validated end to end, in Portuguese, through a pipeline.
// ─────────────────────────────────────────────────────────────────────

#[CoversNothing]
final class RegistrationFlowTest extends TestCase
{
    #[Test]
    public function aCompanyFormIsValidatedWithCustomRulesInPortuguese(): void
    {
        $registry = InMemoryRuleRegistry::withDefaults();
        $registry->register('tax_id', new AtLeastOneOfRule(new CpfRule(), new CnpjRule()));
        $registry->register('cnpj_for_companies', new ConditionalRule(
            static fn(mixed $value, ValidationContext $context): bool => $context->fieldValue('company.type') === 'company',
            new CnpjRule(),
        ));
        $durations = new ArrayObject();
        $engine = new ValidatorEngine(
            registry: $registry,
            configuration: new ValidatorConfiguration(defaultLocale: 'pt_BR', customMessages: ['pt_BR' => ['logic.at_least_one_of' => 'Informe um CPF ou CNPJ válido em {field}.']]),
            observer: static function (object $event) use ($durations): void {
                if ($event instanceof ValidationCompletedEvent) {
                    $durations->append($event->durationMs);
                }
            },
        );
        $rules = [
            'company.type' => ['required', ['choice', ['choices' => ['company', 'person']]]],
            'company.document' => ['required', 'cnpj_for_companies'],
            'owner.document' => ['tax_id'],
            'owner.email' => ['required', 'email'],
            'address.cep' => ['nullable', 'cep'],
        ];

        $valid = $engine->validate(['company' => ['type' => 'company', 'document' => '12.ABC.345/01DE-35'], 'owner' => ['document' => '529.982.247-25', 'email' => 'ana@example.com']], $rules);
        $invalid = $engine->validate(['company' => ['type' => 'company', 'document' => '12.ABC.345/01DE-36'], 'owner' => ['document' => '123', 'email' => 'ana'], 'address' => ['cep' => '00000-000']], $rules);
        $person = $engine->validate(['company' => ['type' => 'person', 'document' => '529.982.247-25'], 'owner' => ['document' => '11.222.333/0001-81', 'email' => 'ana@example.com'], 'address' => ['cep' => '63100-000']], $rules);

        self::assertTrue($valid->isValid());
        self::assertTrue($person->isValid());
        self::assertSame([
            'company.document' => ['O campo company.document não é um CNPJ válido.'],
            'owner.document' => ['Informe um CPF ou CNPJ válido em owner.document.'],
            'owner.email' => ['O campo owner.email deve ser um endereço de e-mail válido.'],
            'address.cep' => ['O campo address.cep não é um CEP válido.'],
        ], $invalid->messages());
        self::assertCount(3, $durations);
    }

    #[Test]
    public function aPipelineStopsAtTheValidationStep(): void
    {
        $engine = new ValidatorEngine();
        $trim = new class implements Processor {
            public function process(mixed $input): mixed
            {
                return is_array($input) ? array_map(static fn(mixed $value): mixed => is_string($value) ? trim($value) : $value, $input) : $input;
            }
        };
        $pipeline = [$trim, new ValidationProcessor($engine, ['email' => ['required', 'email'], 'name' => [['length', ['min' => 2]]]])];
        $run = static function (array $input) use ($pipeline): mixed {
            foreach ($pipeline as $processor) {
                $input = $processor->process($input);
            }

            return $input;
        };

        self::assertSame(['email' => 'ana@example.com', 'name' => 'Ana'], $run(['email' => '  ana@example.com ', 'name' => ' Ana ']));

        try {
            $run(['email' => '   ', 'name' => ' A ']);
            self::fail('The pipeline went past invalid input.');
        } catch (ValidationException $exception) {
            self::assertSame(['email', 'name'], $exception->result->failedFields());
            self::assertSame('[VAL_3230] 3 validation error(s) on "email", "name".', $exception->getMessage());
        }
    }

    #[Test]
    public function aDtoIsValidatedPerGroup(): void
    {
        $validator = new AttributeValidator(new ValidatorEngine(), new ReflectionInspector());
        $create = new SignUp(email: 'ana@example.com', password: 'correct horse battery', passwordConfirmation: 'correct horse battery', nickname: null);
        $update = new SignUp(email: 'ana@example.com', password: 'correct horse battery', passwordConfirmation: 'correct horse battery', id: '550e8400-e29b-41d4-a716-446655440000', backupEmails: ['b@example.com']);

        self::assertTrue($validator->validate($create, 'create')->isValid());
        self::assertFalse($validator->validate($create, 'update')->isValid());
        self::assertTrue($validator->validate($update, 'update')->isValid());
        self::assertTrue($validator->validate($update)->isValid());
    }
}
