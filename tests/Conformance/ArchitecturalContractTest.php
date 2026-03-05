<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Conformance;

use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;
use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\AttributeValidator;
use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Event\ValidationCompletedEvent;
use KaririCode\Validator\Event\ValidationStartedEvent;
use KaririCode\Validator\Integration\ProcessorBridge;
use KaririCode\Validator\Result\ValidationError;
use KaririCode\Validator\Rule\Brazilian\CepRule;
use KaririCode\Validator\Rule\Brazilian\CnpjRule;
use KaririCode\Validator\Rule\Brazilian\CpfRule;
use KaririCode\Validator\Rule\Brazilian\PisRule;
use KaririCode\Validator\Rule\Collection\CountRule;
use KaririCode\Validator\Rule\Collection\EachRule;
use KaririCode\Validator\Rule\Collection\KeyExistsRule;
use KaririCode\Validator\Rule\Collection\UniqueElementsRule;
use KaririCode\Validator\Rule\Comparison\BetweenRule;
use KaririCode\Validator\Rule\Comparison\EqualToRule;
use KaririCode\Validator\Rule\Comparison\GreaterThanOrEqualRule;
use KaririCode\Validator\Rule\Comparison\GreaterThanRule;
use KaririCode\Validator\Rule\Comparison\LessThanOrEqualRule;
use KaririCode\Validator\Rule\Comparison\LessThanRule;
use KaririCode\Validator\Rule\Comparison\NotEqualToRule;
use KaririCode\Validator\Rule\CrossField\DifferentFromRule;
use KaririCode\Validator\Rule\CrossField\RequiredWithoutRule;
use KaririCode\Validator\Rule\CrossField\RequiredWithRule;
use KaririCode\Validator\Rule\CrossField\SameAsRule;
use KaririCode\Validator\Rule\Date\DateAfterRule;
use KaririCode\Validator\Rule\Date\DateBeforeRule;
use KaririCode\Validator\Rule\Date\DateBetweenRule;
use KaririCode\Validator\Rule\Date\DateFormatRule;
use KaririCode\Validator\Rule\Financial\BicRule;
use KaririCode\Validator\Rule\Financial\IbanRule;
use KaririCode\Validator\Rule\Financial\LuhnRule;
use KaririCode\Validator\Rule\Logic\AtLeastOneOfRule;
use KaririCode\Validator\Rule\Logic\CallbackRule;
use KaririCode\Validator\Rule\Logic\ChoiceRule;
use KaririCode\Validator\Rule\Logic\CompositeRule;
use KaririCode\Validator\Rule\Logic\ConditionalRule;
use KaririCode\Validator\Rule\Logic\NotNullRule;
use KaririCode\Validator\Rule\Logic\NullableRule;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use KaririCode\Validator\Rule\Numeric\DecimalRule;
use KaririCode\Validator\Rule\Numeric\IntegerRule;
use KaririCode\Validator\Rule\Numeric\NegativeOrZeroRule;
use KaririCode\Validator\Rule\Numeric\NegativeRule;
use KaririCode\Validator\Rule\Numeric\PositiveRule;
use KaririCode\Validator\Rule\Numeric\RangeRule;
use KaririCode\Validator\Rule\String\AlphanumericRule;
use KaririCode\Validator\Rule\String\AlphaRule;
use KaririCode\Validator\Rule\String\EmailRule;
use KaririCode\Validator\Rule\String\EndsWithRule;
use KaririCode\Validator\Rule\String\HostnameRule;
use KaririCode\Validator\Rule\String\IpRule;
use KaririCode\Validator\Rule\String\JsonRule;
use KaririCode\Validator\Rule\String\LengthRule;
use KaririCode\Validator\Rule\String\NotEmptyRule;
use KaririCode\Validator\Rule\String\PatternRule;
use KaririCode\Validator\Rule\String\SlugRule;
use KaririCode\Validator\Rule\String\StartsWithRule;
use KaririCode\Validator\Rule\String\UlidRule;
use KaririCode\Validator\Rule\String\UrlRule;
use KaririCode\Validator\Rule\String\UuidRule;
use KaririCode\Validator\Rule\Type\InstanceOfRule;
use KaririCode\Validator\Rule\Type\TypeRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verifies structural properties mandated by the KaririCode architecture:
 * - All value objects are `final readonly class`
 * - Zero antipatterns (no toArray, fromArray)
 * - All rules implement ValidationRule and are `final readonly`
 */
final class ArchitecturalContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function readonlyClassProvider(): iterable
    {
        $readonlyClasses = [
            ValidationError::class,
            ValidationContextImpl::class,
            ValidationStartedEvent::class,
            ValidationCompletedEvent::class,
            ValidatorConfiguration::class,
            Validate::class,
            ValidationGroup::class,
            ProcessorBridge::class,
            AttributeValidator::class,
        ];

        foreach ($readonlyClasses as $class) {
            yield $class => [$class];
        }
    }

    #[DataProvider('readonlyClassProvider')]
    public function testValueObjectsAreReadonly(string $class): void
    {
        $ref = new \ReflectionClass($class);
        $this->assertTrue($ref->isReadOnly(), "{$class} must be readonly.");
    }

    #[DataProvider('readonlyClassProvider')]
    public function testValueObjectsAreFinal(string $class): void
    {
        $ref = new \ReflectionClass($class);
        $this->assertTrue($ref->isFinal(), "{$class} must be final.");
    }

    /**
     * All 57 rule classes (52 registered + 5 constructor-dependent).
     *
     * @return iterable<string, array{class-string}>
     */
    public static function allRuleClassProvider(): iterable
    {
        $rules = [
            // String (15)
            EmailRule::class,
            UrlRule::class,
            LengthRule::class,
            PatternRule::class,
            NotEmptyRule::class,
            AlphaRule::class,
            AlphanumericRule::class,
            UuidRule::class,
            UlidRule::class,
            IpRule::class,
            JsonRule::class,
            HostnameRule::class,
            SlugRule::class,
            StartsWithRule::class,
            EndsWithRule::class,
            // Numeric (6)
            RangeRule::class,
            PositiveRule::class,
            IntegerRule::class,
            NegativeRule::class,
            NegativeOrZeroRule::class,
            DecimalRule::class,
            // Comparison (7)
            EqualToRule::class,
            NotEqualToRule::class,
            GreaterThanRule::class,
            GreaterThanOrEqualRule::class,
            LessThanRule::class,
            LessThanOrEqualRule::class,
            BetweenRule::class,
            // Date (4)
            DateFormatRule::class,
            DateBeforeRule::class,
            DateAfterRule::class,
            DateBetweenRule::class,
            // Type (2)
            TypeRule::class,
            InstanceOfRule::class,
            // Logic (8)
            RequiredRule::class,
            ChoiceRule::class,
            CompositeRule::class,
            ConditionalRule::class,
            NullableRule::class,
            NotNullRule::class,
            CallbackRule::class,
            AtLeastOneOfRule::class,
            // Collection (4)
            CountRule::class,
            UniqueElementsRule::class,
            KeyExistsRule::class,
            EachRule::class,
            // CrossField (4)
            SameAsRule::class,
            DifferentFromRule::class,
            RequiredWithRule::class,
            RequiredWithoutRule::class,
            // Financial (3)
            LuhnRule::class,
            IbanRule::class,
            BicRule::class,
            // Brazilian (4)
            CpfRule::class,
            CnpjRule::class,
            CepRule::class,
            PisRule::class,
        ];

        foreach ($rules as $class) {
            yield $class => [$class];
        }
    }

    #[DataProvider('allRuleClassProvider')]
    public function testAllRulesImplementValidationRuleContract(string $class): void
    {
        $ref = new \ReflectionClass($class);
        $this->assertTrue(
            $ref->implementsInterface(ValidationRule::class),
            "{$class} must implement ValidationRule contract.",
        );
    }

    #[DataProvider('allRuleClassProvider')]
    public function testAllRulesAreFinalReadonly(string $class): void
    {
        $ref = new \ReflectionClass($class);
        $this->assertTrue($ref->isFinal(), "{$class} must be final.");
        $this->assertTrue($ref->isReadOnly(), "{$class} must be readonly.");
    }

    public function testNoToArrayAntipatternOnValueObjects(): void
    {
        $ref = new \ReflectionClass(ValidationError::class);
        $this->assertFalse($ref->hasMethod('toArray'), 'toArray antipattern detected.');
    }

    public function testNoFromArrayAntipatternOnValueObjects(): void
    {
        $ref = new \ReflectionClass(ValidationError::class);
        $this->assertFalse($ref->hasMethod('fromArray'), 'fromArray antipattern detected.');
    }

    public function testTotalRuleClassCount(): void
    {
        $count = iterator_count(self::allRuleClassProvider());
        $this->assertSame(57, $count, 'Expected 57 total rule classes (52 registered + 5 constructor-dependent).');
    }
}
