<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Conformance;

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
            \KaririCode\Validator\Result\ValidationError::class,
            \KaririCode\Validator\Core\ValidationContextImpl::class,
            \KaririCode\Validator\Event\ValidationStartedEvent::class,
            \KaririCode\Validator\Event\ValidationCompletedEvent::class,
            \KaririCode\Validator\Configuration\ValidatorConfiguration::class,
            \KaririCode\Validator\Attribute\Validate::class,
            \KaririCode\Validator\Attribute\ValidationGroup::class,
            \KaririCode\Validator\Integration\ProcessorBridge::class,
            \KaririCode\Validator\Core\AttributeValidator::class,
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
            \KaririCode\Validator\Rule\String\EmailRule::class,
            \KaririCode\Validator\Rule\String\UrlRule::class,
            \KaririCode\Validator\Rule\String\LengthRule::class,
            \KaririCode\Validator\Rule\String\PatternRule::class,
            \KaririCode\Validator\Rule\String\NotEmptyRule::class,
            \KaririCode\Validator\Rule\String\AlphaRule::class,
            \KaririCode\Validator\Rule\String\AlphanumericRule::class,
            \KaririCode\Validator\Rule\String\UuidRule::class,
            \KaririCode\Validator\Rule\String\UlidRule::class,
            \KaririCode\Validator\Rule\String\IpRule::class,
            \KaririCode\Validator\Rule\String\JsonRule::class,
            \KaririCode\Validator\Rule\String\HostnameRule::class,
            \KaririCode\Validator\Rule\String\SlugRule::class,
            \KaririCode\Validator\Rule\String\StartsWithRule::class,
            \KaririCode\Validator\Rule\String\EndsWithRule::class,
            // Numeric (6)
            \KaririCode\Validator\Rule\Numeric\RangeRule::class,
            \KaririCode\Validator\Rule\Numeric\PositiveRule::class,
            \KaririCode\Validator\Rule\Numeric\IntegerRule::class,
            \KaririCode\Validator\Rule\Numeric\NegativeRule::class,
            \KaririCode\Validator\Rule\Numeric\NegativeOrZeroRule::class,
            \KaririCode\Validator\Rule\Numeric\DecimalRule::class,
            // Comparison (7)
            \KaririCode\Validator\Rule\Comparison\EqualToRule::class,
            \KaririCode\Validator\Rule\Comparison\NotEqualToRule::class,
            \KaririCode\Validator\Rule\Comparison\GreaterThanRule::class,
            \KaririCode\Validator\Rule\Comparison\GreaterThanOrEqualRule::class,
            \KaririCode\Validator\Rule\Comparison\LessThanRule::class,
            \KaririCode\Validator\Rule\Comparison\LessThanOrEqualRule::class,
            \KaririCode\Validator\Rule\Comparison\BetweenRule::class,
            // Date (4)
            \KaririCode\Validator\Rule\Date\DateFormatRule::class,
            \KaririCode\Validator\Rule\Date\DateBeforeRule::class,
            \KaririCode\Validator\Rule\Date\DateAfterRule::class,
            \KaririCode\Validator\Rule\Date\DateBetweenRule::class,
            // Type (2)
            \KaririCode\Validator\Rule\Type\TypeRule::class,
            \KaririCode\Validator\Rule\Type\InstanceOfRule::class,
            // Logic (8)
            \KaririCode\Validator\Rule\Logic\RequiredRule::class,
            \KaririCode\Validator\Rule\Logic\ChoiceRule::class,
            \KaririCode\Validator\Rule\Logic\CompositeRule::class,
            \KaririCode\Validator\Rule\Logic\ConditionalRule::class,
            \KaririCode\Validator\Rule\Logic\NullableRule::class,
            \KaririCode\Validator\Rule\Logic\NotNullRule::class,
            \KaririCode\Validator\Rule\Logic\CallbackRule::class,
            \KaririCode\Validator\Rule\Logic\AtLeastOneOfRule::class,
            // Collection (4)
            \KaririCode\Validator\Rule\Collection\CountRule::class,
            \KaririCode\Validator\Rule\Collection\UniqueElementsRule::class,
            \KaririCode\Validator\Rule\Collection\KeyExistsRule::class,
            \KaririCode\Validator\Rule\Collection\EachRule::class,
            // CrossField (4)
            \KaririCode\Validator\Rule\CrossField\SameAsRule::class,
            \KaririCode\Validator\Rule\CrossField\DifferentFromRule::class,
            \KaririCode\Validator\Rule\CrossField\RequiredWithRule::class,
            \KaririCode\Validator\Rule\CrossField\RequiredWithoutRule::class,
            // Financial (3)
            \KaririCode\Validator\Rule\Financial\LuhnRule::class,
            \KaririCode\Validator\Rule\Financial\IbanRule::class,
            \KaririCode\Validator\Rule\Financial\BicRule::class,
            // Brazilian (4)
            \KaririCode\Validator\Rule\Brazilian\CpfRule::class,
            \KaririCode\Validator\Rule\Brazilian\CnpjRule::class,
            \KaririCode\Validator\Rule\Brazilian\CepRule::class,
            \KaririCode\Validator\Rule\Brazilian\PisRule::class,
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
            $ref->implementsInterface(\KaririCode\Validator\Contract\ValidationRule::class),
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
        $ref = new \ReflectionClass(\KaririCode\Validator\Result\ValidationError::class);
        $this->assertFalse($ref->hasMethod('toArray'), 'toArray antipattern detected.');
    }

    public function testNoFromArrayAntipatternOnValueObjects(): void
    {
        $ref = new \ReflectionClass(\KaririCode\Validator\Result\ValidationError::class);
        $this->assertFalse($ref->hasMethod('fromArray'), 'fromArray antipattern detected.');
    }

    public function testTotalRuleClassCount(): void
    {
        $count = iterator_count(self::allRuleClassProvider());
        $this->assertSame(57, $count, 'Expected 57 total rule classes (52 registered + 5 constructor-dependent).');
    }
}
