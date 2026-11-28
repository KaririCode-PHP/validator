<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Exception;

use Closure;
use KaririCode\Contract\Exception\Configuration;
use KaririCode\Contract\Exception\Validation;
use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Exception\ConfigurationException;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Exception\UnsupportedInputException;
use KaririCode\Validator\Exception\ValidationException;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Exception\ValidatorException;
use KaririCode\Validator\Result\ValidationError;
use KaririCode\Validator\Result\ValidationResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;

// ── Unit: ExceptionsTest ─────────────────────────────────────────────
// Each named constructor: its code, its family, its message.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(ValidatorCode::class)]
#[CoversClass(InvalidRuleException::class)]
#[CoversClass(ConfigurationException::class)]
#[CoversClass(ValidationException::class)]
#[CoversClass(UnsupportedInputException::class)]
#[CoversClass(ValidatorConfiguration::class)]
final class ExceptionsTest extends TestCase
{
    /**
     * @return iterable<string, array{Closure(): Throwable, ValidatorCode, class-string, string}>
     */
    public static function exceptions(): iterable
    {
        yield 'duplicate alias' => [static fn(): Throwable => InvalidRuleException::duplicateAlias('email'), ValidatorCode::DuplicateRuleAlias, Configuration::class, 'A rule is already registered under the alias "email".'];
        yield 'unknown alias' => [static fn(): Throwable => InvalidRuleException::unknownAlias("ema\nil"), ValidatorCode::UnknownRuleAlias, Configuration::class, 'No rule is registered under the alias "ema\\nil".'];
        yield 'invalid definition' => [static fn(): Throwable => InvalidRuleException::invalidDefinition('name', 'a reason'), ValidatorCode::InvalidRuleDefinition, Configuration::class, 'Invalid rule definition for field "name": a reason.'];
        yield 'empty alias' => [static fn(): Throwable => InvalidRuleException::emptyAlias(), ValidatorCode::InvalidRuleDefinition, Configuration::class, 'A rule alias must not be empty.'];
        yield 'no rules' => [static fn(): Throwable => InvalidRuleException::noRules('CompositeRule'), ValidatorCode::InvalidRuleDefinition, Configuration::class, 'CompositeRule needs at least one rule.'];
        yield 'missing parameter' => [static fn(): Throwable => InvalidRuleException::missingParameter('age', 'min'), ValidatorCode::MissingRuleParameter, Configuration::class, 'A rule on field "age" needs the parameter "min".'];
        yield 'missing bounds' => [static fn(): Throwable => InvalidRuleException::missingBounds('age'), ValidatorCode::MissingRuleParameter, Configuration::class, 'A rule on field "age" needs the parameter "min", "max" or both.'];
        yield 'invalid parameter' => [static fn(): Throwable => InvalidRuleException::invalidParameter(field: 'age', parameter: 'min', expected: 'an integer', given: 'string'), ValidatorCode::InvalidRuleParameter, Configuration::class, 'The parameter "min" of a rule on field "age" must be an integer, string given.'];
        yield 'invalid parameter, nothing given' => [static fn(): Throwable => InvalidRuleException::invalidParameter(field: 'age', parameter: 'choices', expected: 'a non-empty list'), ValidatorCode::InvalidRuleParameter, Configuration::class, 'The parameter "choices" of a rule on field "age" must be a non-empty list.'];
        yield 'named arguments' => [static fn(): Throwable => InvalidRuleException::namedArguments('Validate', ['min']), ValidatorCode::NamedAttributeArguments, Configuration::class, "#[Validate] takes no named arguments (min); pass parameters as ['alias', ['parameter' => value]]."];
        yield 'invalid setting' => [static fn(): Throwable => ConfigurationException::invalidSetting('failFast', 'is wrong'), ValidatorCode::InvalidSetting, Configuration::class, 'The validator setting "failFast" is wrong.'];
        yield 'invalid binding' => [static fn(): Throwable => ConfigurationException::invalidBinding('App\\Service', 'string'), ValidatorCode::InvalidBinding, Configuration::class, 'The container resolves "App\\\\Service" to string, which is not an instance of it.'];
        yield 'validation failed' => [static fn(): Throwable => ValidationException::failed(new ValidationResult(new ValidationError('a', 'x', 'm'), new ValidationError('b', 'y', 'n'), new ValidationError('a', 'z', 'o'))), ValidatorCode::ValidationFailed, Validation::class, '3 validation error(s) on "a", "b".'];
        yield 'unsupported input' => [static fn(): Throwable => UnsupportedInputException::notAnArray('string'), ValidatorCode::UnsupportedInput, Validation::class, 'The validation processor expects an array of fields, string given.'];
    }

    /**
     * @param Closure(): Throwable $build
     * @param class-string         $family
     */
    #[Test]
    #[DataProvider('exceptions')]
    public function eachExceptionCarriesItsCodeFamilyAndMessage(Closure $build, ValidatorCode $code, string $family, string $message): void
    {
        $exception = $build();
        self::assertInstanceOf(ValidatorException::class, $exception);
        self::assertInstanceOf($family, $exception);
        self::assertSame($code, $exception->errorCode);
        self::assertSame($code->value, $exception->getCode());
        self::assertSame("[VAL_{$code->value}] {$message}", $exception->getMessage());
    }

    #[Test]
    public function aValidationExceptionCarriesTheResult(): void
    {
        $result = new ValidationResult(new ValidationError('email', 'string.email', 'Bad.'));

        self::assertSame($result, ValidationException::failed($result)->result);
    }

    #[Test]
    public function theCodeTableNamesItsPrefixAndBlock(): void
    {
        self::assertSame('VAL', ValidatorCode::prefix());
        self::assertSame([3200, 3299], [ValidatorCode::block()->first, ValidatorCode::block()->last]);
    }

    /**
     * @return iterable<string, array{Closure(): ValidatorConfiguration, non-empty-string}>
     */
    public static function badSettings(): iterable
    {
        $catalog = '"customMessages" must map locales to maps of error codes to strings.';
        yield 'empty locale' => [static fn(): ValidatorConfiguration => new ValidatorConfiguration(defaultLocale: ''), '"defaultLocale" must not be empty.'];
        yield 'negative limit' => [static fn(): ValidatorConfiguration => new ValidatorConfiguration(maxErrorsPerField: -1), '"maxErrorsPerField" must be 0 (no limit) or more.'];
        yield 'messages not per locale' => [static fn(): ValidatorConfiguration => new ValidatorConfiguration(customMessages: ['custom.even' => 'Even.']), $catalog];
        yield 'locale as an integer' => [static fn(): ValidatorConfiguration => new ValidatorConfiguration(customMessages: [['custom.even' => 'Even.']]), $catalog];
        yield 'code as an integer' => [static fn(): ValidatorConfiguration => new ValidatorConfiguration(customMessages: ['en' => ['Even.']]), $catalog];
        yield 'message not a string' => [static fn(): ValidatorConfiguration => new ValidatorConfiguration(customMessages: ['en' => ['custom.even' => 1]]), $catalog];
    }

    /**
     * @param Closure(): ValidatorConfiguration $build
     * @param non-empty-string                  $ending
     */
    #[Test]
    #[DataProvider('badSettings')]
    public function invalidSettingsAreRefused(Closure $build, string $ending): void
    {
        try {
            $build();
            self::fail('The setting was accepted.');
        } catch (ConfigurationException $exception) {
            self::assertSame(ValidatorCode::InvalidSetting, $exception->errorCode);
            self::assertStringEndsWith($ending, $exception->getMessage());
        }
    }

    #[Test]
    public function theConfigurationHasCollectAllDefaults(): void
    {
        $configuration = new ValidatorConfiguration();

        self::assertSame(['en', false, 0, []], [$configuration->defaultLocale, $configuration->failFast, $configuration->maxErrorsPerField, $configuration->customMessages]);
        self::assertSame(['en' => ['a' => 'b', 'c' => 'd'], 'es' => ['e' => 'f']], (new ValidatorConfiguration(customMessages: ['en' => ['a' => 'b', 'c' => 'd'], 'es' => ['e' => 'f']]))->customMessages);
    }
}
