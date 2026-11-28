<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\Blank;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Rule\Logic\AtLeastOneOfRule;
use KaririCode\Validator\Rule\Logic\CallbackRule;
use KaririCode\Validator\Rule\Logic\ChoiceRule;
use KaririCode\Validator\Rule\Logic\CompositeRule;
use KaririCode\Validator\Rule\Logic\ConditionalRule;
use KaririCode\Validator\Rule\Logic\NotNullRule;
use KaririCode\Validator\Rule\Logic\NullableRule;
use KaririCode\Validator\Rule\Logic\RequiredRule;
use KaririCode\Validator\Rule\String\EmailRule;
use KaririCode\Validator\Rule\String\LengthRule;
use KaririCode\Validator\Tests\Support\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

// ── Unit: LogicRulesTest ─────────────────────────────────────────────
// required, not_null, nullable, choice and the composite rules.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(RequiredRule::class)]
#[CoversClass(NotNullRule::class)]
#[CoversClass(NullableRule::class)]
#[CoversClass(ChoiceRule::class)]
#[CoversClass(CallbackRule::class)]
#[CoversClass(CompositeRule::class)]
#[CoversClass(ConditionalRule::class)]
#[CoversClass(AtLeastOneOfRule::class)]
#[CoversClass(Blank::class)]
final class LogicRulesTest extends RuleTestCase
{
    /**
     * @return iterable<string, array{bool, ValidationRule, mixed, 3?: array<string, mixed>}>
     */
    public static function cases(): iterable
    {
        $required = new RequiredRule();
        yield 'required: text' => [true, $required, 'a'];
        yield 'required: zero' => [true, $required, 0];
        yield 'required: string zero' => [true, $required, '0'];
        yield 'required: false' => [true, $required, false];
        yield 'required: list' => [true, $required, [0]];
        yield 'required: null' => [false, $required, null];
        yield 'required: empty string' => [false, $required, ''];
        yield 'required: spaces' => [false, $required, '   '];
        yield 'required: tab and newline' => [false, $required, "\t\n"];
        yield 'required: empty array' => [false, $required, []];

        $notNull = new NotNullRule();
        yield 'not_null: empty string' => [true, $notNull, ''];
        yield 'not_null: false' => [true, $notNull, false];
        yield 'not_null: empty array' => [true, $notNull, []];
        yield 'not_null: null' => [false, $notNull, null];

        yield 'nullable: null' => [true, new NullableRule(), null];
        yield 'nullable: a value' => [true, new NullableRule(), 'x'];

        $choice = new ChoiceRule();
        $choices = ['choices' => ['draft', 'published', 1]];
        yield 'choice: listed' => [true, $choice, 'draft', $choices];
        yield 'choice: listed integer' => [true, $choice, 1, $choices];
        yield 'choice: not listed' => [false, $choice, 'archived', $choices];
        yield 'choice: same digits, other type' => [false, $choice, '1', $choices];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[Test]
    #[DataProvider('cases')]
    public function rulesAcceptAndRejectValues(bool $expected, ValidationRule $rule, mixed $value, array $parameters = []): void
    {
        self::assertRule($expected, $rule, $value, $parameters);
    }

    #[Test]
    public function violationsNameTheirCodes(): void
    {
        self::assertViolation('logic.required', new RequiredRule());
        self::assertViolation('logic.not_null', new NotNullRule());
        self::assertViolation('logic.choice', new ChoiceRule());
        self::assertViolation('logic.at_least_one_of', new AtLeastOneOfRule(new EmailRule()));

        $nullable = (new NullableRule())->violation(self::context());
        self::assertSame(['logic.nullable', 'The {field} must be null or valid.'], [$nullable->code, $nullable->message]);
    }

    #[Test]
    public function choiceNeedsANonEmptyList(): void
    {
        self::assertRefused(ValidatorCode::MissingRuleParameter, new ChoiceRule(), []);
        self::assertRefused(ValidatorCode::InvalidRuleParameter, new ChoiceRule(), ['choices' => []]);
        self::assertRefused(ValidatorCode::InvalidRuleParameter, new ChoiceRule(), ['choices' => 'draft']);
    }

    #[Test]
    public function aCallbackPassesOnlyWhenItReturnsTrue(): void
    {
        $seen = new \ArrayObject();
        $rule = new CallbackRule(static function (mixed $value, ValidationContext $context) use ($seen): bool {
            $seen->append([$value, $context->field]);

            return $value === 'yes';
        });

        self::assertRule(true, $rule, 'yes');
        self::assertRule(false, $rule, 'no');
        self::assertSame([['yes', 'field'], ['no', 'field']], $seen->getArrayCopy());
        self::assertRule(false, new CallbackRule(static fn(): int => 1), 'x');
        self::assertViolation('logic.callback', $rule);

        $custom = (new CallbackRule(static fn(): bool => false, errorCode: 'custom.even', message: 'The {field} must be even.'))->violation(self::context());
        self::assertSame(['custom.even', 'The {field} must be even.'], [$custom->code, $custom->message]);
    }

    #[Test]
    public function aCompositePassesWhenEveryRulePassesAndReportsOnce(): void
    {
        $rule = new CompositeRule('custom.contact', 'The {field} is not a usable contact.', new EmailRule(), new LengthRule());

        self::assertRule(true, $rule, 'ana@example.com', ['max' => 20]);
        self::assertRule(false, $rule, 'ana@example.com', ['max' => 5]);
        self::assertRule(false, $rule, 'not an email', ['max' => 20]);

        $violation = $rule->violation(self::context());
        self::assertSame(['custom.contact', 'The {field} is not a usable contact.'], [$violation->code, $violation->message]);
    }

    #[Test]
    public function aConditionalRuleAppliesOnlyWhenItsConditionIsTrue(): void
    {
        $forCompanies = new ConditionalRule(
            static fn(mixed $value, ValidationContext $context): bool => $context->fieldValue('type') === 'company',
            new EmailRule(),
        );

        self::assertRule(false, $forCompanies, 'x', [], ['type' => 'company']);
        self::assertRule(true, $forCompanies, 'ana@example.com', [], ['type' => 'company']);
        self::assertRule(true, $forCompanies, 'x', [], ['type' => 'person']);
        self::assertRule(true, new ConditionalRule(static fn(): int => 1, new EmailRule()), 'x');
        self::assertViolation('string.email', $forCompanies);
    }

    #[Test]
    public function atLeastOneOfPassesWhenAnyRulePasses(): void
    {
        $rule = new AtLeastOneOfRule(new EmailRule(), new RequiredRule());

        self::assertRule(true, $rule, 'not an email');
        self::assertRule(true, $rule, 'ana@example.com');
        self::assertRule(false, $rule, '');
        self::assertRule(false, new AtLeastOneOfRule(email: new EmailRule()), 'x');
        self::assertRule(false, new CompositeRule('c', 'm', email: new EmailRule()), 'x');
    }

    #[Test]
    public function compositesNeedAtLeastOneRule(): void
    {
        foreach ([static fn(): CompositeRule => new CompositeRule('c', 'm'), static fn(): AtLeastOneOfRule => new AtLeastOneOfRule()] as $build) {
            try {
                $build();
                self::fail('An empty composite was built.');
            } catch (InvalidRuleException $exception) {
                self::assertSame(ValidatorCode::InvalidRuleDefinition, $exception->errorCode);
                self::assertStringEndsWith('needs at least one rule.', $exception->getMessage());
            }
        }
    }
}
