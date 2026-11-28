<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Integration;

use ArrayObject;
use KaririCode\Contract\Inspection\PropertyInspector;
use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Contract\MessageResolver;
use KaririCode\Validator\Contract\RuleRegistry;
use KaririCode\Validator\Core\AttributeValidator;
use KaririCode\Validator\Core\InMemoryRuleRegistry;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Exception\ConfigurationException;
use KaririCode\Validator\Exception\UnsupportedInputException;
use KaririCode\Validator\Exception\ValidationException;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Integration\ValidationProcessor;
use KaririCode\Validator\Provider\ValidatorServiceProvider;
use KaririCode\Validator\Tests\Support\MemoryContainer;
use KaririCode\Validator\Tests\Support\ReflectionInspector;
use KaririCode\Validator\Tests\Support\SignUp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

// ── Unit: IntegrationTest ────────────────────────────────────────────
// The contract Processor and Validator, and the service provider.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(ValidationProcessor::class)]
#[CoversClass(ValidatorServiceProvider::class)]
final class IntegrationTest extends TestCase
{
    #[Test]
    public function theProcessorPassesValidInputThroughUnchanged(): void
    {
        $processor = new ValidationProcessor(new ValidatorEngine(), ['email' => ['required', 'email']]);
        $input = ['email' => 'ana@example.com', 'extra' => 1];

        self::assertSame($input, $processor->process($input));
        self::assertTrue($processor->validate($input)->isValid());
    }

    #[Test]
    public function theProcessorStopsAtInvalidInput(): void
    {
        $processor = new ValidationProcessor(new ValidatorEngine(), ['email' => ['required', 'email']]);

        try {
            $processor->process(['email' => 'ana']);
            self::fail('Invalid input passed.');
        } catch (ValidationException $exception) {
            self::assertSame(['email' => ['The email must be a valid email address.']], $exception->result->messages());
        }

        self::assertSame(['email'], $processor->validate(['email' => ''])->failedFields());
    }

    #[Test]
    public function theProcessorPassesItsGroupToTheRules(): void
    {
        $groups = new ArrayObject();
        $registry = InMemoryRuleRegistry::withDefaults();
        $registry->register('spy', new \KaririCode\Validator\Rule\Logic\CallbackRule(static function (mixed $value, \KaririCode\Validator\Contract\ValidationContext $context) use ($groups): bool {
            $groups->append($context->group);

            return true;
        }));

        (new ValidationProcessor(new ValidatorEngine($registry), ['a' => ['spy']], 'update'))->process(['a' => 1]);

        self::assertSame(['update'], $groups->getArrayCopy());
    }

    #[Test]
    public function theProcessorValidatesArraysOnly(): void
    {
        $processor = new ValidationProcessor(new ValidatorEngine(), ['email' => ['email']]);

        foreach (['ana@example.com', new stdClass()] as $input) {
            try {
                $processor->process($input);
                self::fail('A non-array was validated.');
            } catch (UnsupportedInputException $exception) {
                self::assertSame(ValidatorCode::UnsupportedInput, $exception->errorCode);
                self::assertStringContainsString(get_debug_type($input) . ' given', $exception->getMessage());
            }
        }
    }

    #[Test]
    public function theProviderBindsEverySingleton(): void
    {
        $container = new MemoryContainer();
        $configuration = new ValidatorConfiguration(defaultLocale: 'pt_BR');
        $events = new ArrayObject();
        (new ValidatorServiceProvider($configuration, static function (object $event) use ($events): void {
            $events->append($event::class);
        }))->register($container);
        $container->instance(PropertyInspector::class, new ReflectionInspector());

        self::assertSame([RuleRegistry::class, MessageResolver::class, ValidatorEngine::class, AttributeValidator::class], $container->singletons);
        $engine = $container->get(ValidatorEngine::class);
        self::assertInstanceOf(ValidatorEngine::class, $engine);
        self::assertSame($configuration, $engine->configuration);
        self::assertSame($container->get(RuleRegistry::class), $engine->registry);
        self::assertSame($container->get(MessageResolver::class), $engine->messages);
        self::assertSame(['nome' => ['O campo nome é obrigatório.']], $engine->validate([], ['nome' => ['required']])->messages());
        self::assertCount(2, $events);

        $validator = $container->get(AttributeValidator::class);
        self::assertInstanceOf(AttributeValidator::class, $validator);
        self::assertSame(['email', 'password', 'passwordConfirmation'], $validator->validate(new SignUp(email: 'x', password: 'y', passwordConfirmation: 'z'), 'create')->failedFields());
        (new ValidatorServiceProvider())->boot();
    }

    #[Test]
    public function aBindingOfTheWrongTypeIsReported(): void
    {
        $container = new MemoryContainer();
        (new ValidatorServiceProvider())->register($container);
        $container->instance(RuleRegistry::class, new stdClass());

        $this->expectExceptionObject(ConfigurationException::invalidBinding(id: RuleRegistry::class, type: 'stdClass'));
        $this->expectExceptionCode(ValidatorCode::InvalidBinding->value);

        $container->get(ValidatorEngine::class);
    }
}
