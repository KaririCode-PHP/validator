<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use ArrayObject;
use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Contract\MessageResolver;
use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Core\DefaultMessageResolver;
use KaririCode\Validator\Core\InMemoryRuleRegistry;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Event\ValidationCompletedEvent;
use KaririCode\Validator\Event\ValidationStartedEvent;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Result\ValidationError;
use KaririCode\Validator\Rule\Logic\CallbackRule;
use KaririCode\Validator\Rule\String\EmailRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

// ── Unit: ValidatorEngineTest ────────────────────────────────────────
// Rule maps, definitions, collect-all, limits, nullable, messages, events.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(ValidatorEngine::class)]
#[CoversClass(InMemoryRuleRegistry::class)]
#[CoversClass(ValidationStartedEvent::class)]
#[CoversClass(ValidationCompletedEvent::class)]
final class ValidatorEngineTest extends TestCase
{
    #[Test]
    public function everyFailingRuleOfEveryFieldIsReportedInOrder(): void
    {
        $result = (new ValidatorEngine())->validate(
            ['email' => 'x', 'name' => '', 'age' => 30],
            ['email' => ['required', 'email', ['length', ['min' => 5]]], 'name' => ['required', 'alpha'], 'age' => ['integer']],
        );

        self::assertSame(
            [['email', 'string.email'], ['email', 'string.length_min'], ['name', 'logic.required'], ['name', 'string.alpha']],
            array_map(static fn(ValidationError $error): array => [$error->field, $error->errorCode], $result->errors),
        );
        self::assertSame('The email must be at least 5 characters.', $result->errors[1]->message);
        self::assertSame(['min' => 5], $result->errors[1]->parameters);
    }

    #[Test]
    public function definitionsMayBeAliasesRulesOrPairs(): void
    {
        $even = new CallbackRule(static fn(mixed $value): bool => is_int($value) && $value % 2 === 0, errorCode: 'custom.even', message: 'The {field} must be even, not {given}.');
        $result = (new ValidatorEngine())->validate(
            ['a' => 3, 'b' => 3, 'c' => 'ana', 'd' => 'ana'],
            ['a' => [$even], 'b' => [[$even, ['given' => 3]]], 'c' => [['length', ['max' => 2]]], 'd' => [[new EmailRule()]]],
        );

        self::assertSame(['custom.even', 'custom.even', 'string.length_max', 'string.email'], array_column(array_map(static fn(ValidationError $e): array => (array) $e, $result->errors), 'errorCode'));
        self::assertSame('The a must be even, not {given}.', $result->errors[0]->message);
        self::assertSame('The b must be even, not 3.', $result->errors[1]->message);
    }

    /**
     * @return iterable<string, array{mixed, string}>
     */
    public static function invalidDefinitions(): iterable
    {
        yield 'rules not a list' => ['email', 'the rules of a field are a list'];
        yield 'rules as a map' => [['first' => 'email'], 'the rules of a field are a list'];
        yield 'a number' => [[42], 'a rule is an alias, a ValidationRule, or [alias or rule, parameters]'];
        yield 'an empty pair' => [[[]], 'the first item names the rule, by alias or as a ValidationRule'];
        yield 'three items' => [[['length', ['min' => 1], 'extra']], 'a rule is an alias, a ValidationRule, or [alias or rule, parameters]'];
        yield 'a map' => [[['rule' => 'length']], 'a rule is an alias, a ValidationRule, or [alias or rule, parameters]'];
        yield 'a rule that is a number' => [[[42, []]], 'the first item names the rule, by alias or as a ValidationRule'];
        yield 'parameters not an array' => [[['length', 5]], 'the parameters of a rule are a map of names to values'];
        yield 'parameters as a list' => [[['length', [5, 10]]], 'the parameters of a rule are a map of names to values'];
        yield 'parameters partly a list' => [[['length', ['min' => 1, 10]]], 'the parameters of a rule are a map of names to values'];
    }

    #[Test]
    #[DataProvider('invalidDefinitions')]
    public function malformedDefinitionsAreRefused(mixed $rules, string $reason): void
    {
        try {
            (new ValidatorEngine())->validate(['name' => 'x'], ['name' => $rules]);
            self::fail('A malformed definition was accepted.');
        } catch (InvalidRuleException $exception) {
            self::assertSame(ValidatorCode::InvalidRuleDefinition, $exception->errorCode);
            self::assertSame('[VAL_3203] Invalid rule definition for field "name": ' . $reason . '.', $exception->getMessage());
        }
    }

    #[Test]
    public function theEngineUsesTheRegistryAndAllTheParametersItIsGiven(): void
    {
        $registry = new InMemoryRuleRegistry();
        $registry->register('adult', new \KaririCode\Validator\Rule\Numeric\RangeRule());
        $engine = new ValidatorEngine($registry);

        self::assertSame($registry, $engine->registry);
        self::assertSame(['age' => ['The age must be between 18 and 130.']], $engine->validate(['age' => 131], ['age' => [['adult', ['min' => 18, 'max' => 130]]]])->messages());
        self::assertSame(['min' => 18, 'max' => 130], $engine->validate(['age' => 1], ['age' => [['adult', ['min' => 18, 'max' => 130]]]])->errors[0]->parameters);
    }

    #[Test]
    public function anUnknownAliasIsRefused(): void
    {
        $this->expectExceptionObject(InvalidRuleException::unknownAlias('emial'));

        (new ValidatorEngine())->validate(['email' => 'x'], ['email' => [['emial', []]]]);
    }

    #[Test]
    public function nullableSkipsTheOtherRulesOnlyForNull(): void
    {
        $engine = new ValidatorEngine();
        $rules = ['nick' => [['length', ['min' => 3]], 'nullable']];

        self::assertTrue($engine->validate(['nick' => null], $rules)->isValid());
        self::assertTrue($engine->validate([], $rules)->isValid());
        self::assertSame(['nick' => ['The nick must be at least 3 characters.']], $engine->validate(['nick' => 'ab'], $rules)->messages());
        self::assertSame(['nick' => ['The nick field is required.']], $engine->validate(['nick' => null], ['nick' => ['required']])->messages());
    }

    #[Test]
    public function failFastStopsAFieldAtItsFirstError(): void
    {
        $engine = new ValidatorEngine(configuration: new ValidatorConfiguration(failFast: true));
        $result = $engine->validate(['a' => '', 'b' => 'x'], ['a' => ['required', 'email'], 'b' => ['email', 'integer']]);

        self::assertSame(['a' => ['The a field is required.'], 'b' => ['The b must be a valid email address.']], $result->messages());
    }

    #[Test]
    public function maxErrorsPerFieldCapsEachField(): void
    {
        $rules = ['a' => ['email', 'integer', 'uuid'], 'b' => ['email', 'integer', 'uuid']];

        self::assertSame(4, (new ValidatorEngine(configuration: new ValidatorConfiguration(maxErrorsPerField: 2)))->validate(['a' => 'x', 'b' => 'y'], $rules)->errorCount());
        self::assertSame(2, (new ValidatorEngine(configuration: new ValidatorConfiguration(maxErrorsPerField: 1)))->validate(['a' => 'x', 'b' => 'y'], $rules)->errorCount());
        self::assertSame(6, (new ValidatorEngine())->validate(['a' => 'x', 'b' => 'y'], $rules)->errorCount());
    }

    #[Test]
    public function fieldsAreReadByDottedPathAndRulesSeeTheWholeData(): void
    {
        $seen = new ArrayObject();
        $spy = new CallbackRule(static function (mixed $value, ValidationContext $context) use ($seen): bool {
            $seen->append([$value, $context->field, $context->group, $context->data]);

            return true;
        });
        $data = ['address' => ['city' => 'Crato'], 7 => 'seven'];

        (new ValidatorEngine())->validate($data, ['address.city' => [$spy], 7 => [$spy]], 'checkout');

        self::assertSame([['Crato', 'address.city', 'checkout', $data], ['seven', '7', 'checkout', $data]], $seen->getArrayCopy());
    }

    #[Test]
    public function messagesComeFromTheResolverWithTheRuleAsFallback(): void
    {
        $resolver = new class implements MessageResolver {
            public function resolve(string $errorCode, string $fallback, array $parameters = [], ?string $locale = null): string
            {
                return $errorCode . '|' . $fallback . '|' . json_encode($parameters) . '|' . var_export($locale, true);
            }
        };

        $result = (new ValidatorEngine(messages: $resolver))->validate(['a' => 'x'], ['a' => [['length', ['min' => 3]]]]);

        self::assertSame('string.length_min|The {field} must be at least {min} characters.|{"min":3,"field":"a"}|NULL', $result->errors[0]->message);
    }

    #[Test]
    public function theDefaultsComeFromTheConfiguration(): void
    {
        $configuration = new ValidatorConfiguration(defaultLocale: 'pt_BR', customMessages: ['pt_BR' => ['logic.required' => 'Preencha {field}.']]);
        $engine = new ValidatorEngine(configuration: $configuration);

        self::assertSame($configuration, $engine->configuration);
        self::assertInstanceOf(DefaultMessageResolver::class, $engine->messages);
        self::assertCount(52, $engine->registry->aliases());
        self::assertSame(['nome' => ['Preencha nome.']], $engine->validate([], ['nome' => ['required']])->messages());
        self::assertSame(['idade' => ['O campo idade deve ser um número inteiro.']], $engine->validate(['idade' => 'x'], ['idade' => ['integer']])->messages());
    }

    #[Test]
    public function theObserverSeesTheStartAndTheEnd(): void
    {
        /** @var ArrayObject<int, object> $events */
        $events = new ArrayObject();
        $engine = new ValidatorEngine(observer: static function (object $event) use ($events): void {
            $events->append($event);
        });

        $result = $engine->validate(['a' => 'x'], ['a' => ['email'], 3 => ['nullable']], 'signup');

        self::assertCount(2, $events);
        $started = $events[0];
        $completed = $events[1];
        self::assertInstanceOf(ValidationStartedEvent::class, $started);
        self::assertInstanceOf(ValidationCompletedEvent::class, $completed);
        self::assertSame(['a', '3'], $started->fields);
        self::assertSame('signup', $started->group);
        self::assertEqualsWithDelta(microtime(true), $started->timestamp, 5.0);
        self::assertSame($result, $completed->result);
        self::assertGreaterThan(0.0, $completed->durationMs);
        self::assertLessThan(5000.0, $completed->durationMs);
    }

    #[Test]
    public function theRegistryKeepsRulesByAlias(): void
    {
        $registry = new InMemoryRuleRegistry();
        $rule = new EmailRule();
        $registry->register('mail', $rule);

        self::assertSame($rule, $registry->resolve('mail'));
        self::assertTrue($registry->has('mail'));
        self::assertFalse($registry->has('email'));
        self::assertSame(['mail'], $registry->aliases());

        foreach ([
            [static fn() => $registry->register('mail', $rule), ValidatorCode::DuplicateRuleAlias, 'A rule is already registered under the alias "mail".'],
            [static fn() => $registry->register('', $rule), ValidatorCode::InvalidRuleDefinition, 'A rule alias must not be empty.'],
            [static fn() => $registry->resolve('email'), ValidatorCode::UnknownRuleAlias, 'No rule is registered under the alias "email".'],
        ] as [$call, $code, $message]) {
            try {
                $call();
                self::fail($message);
            } catch (InvalidRuleException $exception) {
                self::assertSame($code, $exception->errorCode);
                self::assertStringEndsWith($message, $exception->getMessage());
            }
        }
    }

    #[Test]
    public function theDefaultRegistryHoldsTheFiftyTwoParameterizedRules(): void
    {
        $aliases = InMemoryRuleRegistry::withDefaults()->aliases();

        self::assertCount(52, $aliases);
        self::assertSame(['required', 'not_null', 'nullable', 'choice', 'email'], array_slice($aliases, 0, 5));
        self::assertContains('cnpj', $aliases);
        self::assertNotContains('each', $aliases);
    }
}
