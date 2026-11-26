<?php

declare(strict_types=1);

namespace KaririCode\Validator\Provider;

use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Contract\MessageResolver;
use KaririCode\Validator\Contract\RuleRegistry;
use KaririCode\Validator\Core\AttributeValidator;
use KaririCode\Validator\Core\DefaultMessageResolver;
use KaririCode\Validator\Core\InMemoryRuleRegistry;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Rule\Brazilian\CepRule;
use KaririCode\Validator\Rule\Brazilian\CnpjRule;
use KaririCode\Validator\Rule\Brazilian\CpfRule;
use KaririCode\Validator\Rule\Brazilian\PisRule;
use KaririCode\Validator\Rule\Collection\CountRule;
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
use KaririCode\Validator\Rule\Logic\ChoiceRule;
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

/**
 * Service provider registering all 52 built-in validation rules.
 *
 * Bootstraps the validator ecosystem with all rule categories:
 * Logic (4), String (15), Numeric (6), Comparison (7), Date (4),
 * Type (2), Collection (3), CrossField (4), Financial (3), Brazilian (4).
 *
 * Additionally, 5 rules require constructor arguments and are used
 * programmatically: Callback, AtLeastOneOf, Each, Composite, Conditional.
 *
 * Custom rules can be registered after calling `createRegistry()`.
 *
 * @package KaririCode\Validator\Provider
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class ValidatorServiceProvider
{
    /**
     * Creates a registry with all 52 built-in rules pre-registered.
     */
    public function createRegistry(): RuleRegistry
    {
        $registry = new InMemoryRuleRegistry();

        // ── Logic (4 stateless) ───────────────────────────────────────
        // Callback, AtLeastOneOf, Each, Composite, Conditional require
        // constructor args — registered programmatically by the user.
        $registry->register('required', new RequiredRule());
        $registry->register('choice', new ChoiceRule());
        $registry->register('not_null', new NotNullRule());
        $registry->register('nullable', new NullableRule());

        // ── String (15) ───────────────────────────────────────────────
        $registry->register('email', new EmailRule());
        $registry->register('url', new UrlRule());
        $registry->register('length', new LengthRule());
        $registry->register('pattern', new PatternRule());
        $registry->register('not_empty', new NotEmptyRule());
        $registry->register('alpha', new AlphaRule());
        $registry->register('alphanumeric', new AlphanumericRule());
        $registry->register('uuid', new UuidRule());
        $registry->register('ulid', new UlidRule());
        $registry->register('ip', new IpRule());
        $registry->register('json', new JsonRule());
        $registry->register('hostname', new HostnameRule());
        $registry->register('slug', new SlugRule());
        $registry->register('starts_with', new StartsWithRule());
        $registry->register('ends_with', new EndsWithRule());

        // ── Numeric (6) ───────────────────────────────────────────────
        $registry->register('range', new RangeRule());
        $registry->register('positive', new PositiveRule());
        $registry->register('integer', new IntegerRule());
        $registry->register('negative', new NegativeRule());
        $registry->register('negative_or_zero', new NegativeOrZeroRule());
        $registry->register('decimal', new DecimalRule());

        // ── Comparison (7) ────────────────────────────────────────────
        $registry->register('equal_to', new EqualToRule());
        $registry->register('not_equal_to', new NotEqualToRule());
        $registry->register('greater_than', new GreaterThanRule());
        $registry->register('greater_than_or_equal', new GreaterThanOrEqualRule());
        $registry->register('less_than', new LessThanRule());
        $registry->register('less_than_or_equal', new LessThanOrEqualRule());
        $registry->register('between', new BetweenRule());

        // ── Date (4) ──────────────────────────────────────────────────
        $registry->register('date_format', new DateFormatRule());
        $registry->register('date_before', new DateBeforeRule());
        $registry->register('date_after', new DateAfterRule());
        $registry->register('date_between', new DateBetweenRule());

        // ── Type (2) ──────────────────────────────────────────────────
        $registry->register('type', new TypeRule());
        $registry->register('instance_of', new InstanceOfRule());

        // ── Collection (3) ────────────────────────────────────────────
        $registry->register('count', new CountRule());
        $registry->register('unique', new UniqueElementsRule());
        $registry->register('key_exists', new KeyExistsRule());

        // ── Cross-field (4) ───────────────────────────────────────────
        $registry->register('same_as', new SameAsRule());
        $registry->register('different_from', new DifferentFromRule());
        $registry->register('required_with', new RequiredWithRule());
        $registry->register('required_without', new RequiredWithoutRule());

        // ── Financial (3) ─────────────────────────────────────────────
        $registry->register('luhn', new LuhnRule());
        $registry->register('iban', new IbanRule());
        $registry->register('bic', new BicRule());

        // ── Brazilian (4) ─────────────────────────────────────────────
        $registry->register('cpf', new CpfRule());
        $registry->register('cnpj', new CnpjRule());
        $registry->register('cep', new CepRule());
        $registry->register('pis', new PisRule());

        return $registry;
    }

    /**
     * Creates a fully configured ValidatorEngine with i18n support.
     *
     * @param ValidatorConfiguration|null $configuration Engine configuration
     * @param MessageResolver|null        $messageResolver Custom resolver (null = DefaultMessageResolver)
     */
    public function createEngine(
        ?ValidatorConfiguration $configuration = null,
        ?MessageResolver $messageResolver = null,
    ): ValidatorEngine {
        return new ValidatorEngine(
            registry: $this->createRegistry(),
            messageResolver: $messageResolver ?? new DefaultMessageResolver(
                ($configuration instanceof ValidatorConfiguration ? $configuration->defaultLocale : 'en'),
            ),
            configuration: $configuration,
        );
    }

    /**
     * Creates an AttributeValidator for DTO/object validation via PHP 8.4 attributes.
     */
    public function createAttributeValidator(
        ?ValidatorConfiguration $configuration = null,
        ?MessageResolver $messageResolver = null,
    ): AttributeValidator {
        return new AttributeValidator($this->createEngine($configuration, $messageResolver));
    }
}
