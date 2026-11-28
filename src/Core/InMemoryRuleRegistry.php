<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\Validator\Contract\RuleRegistry;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;
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

// ── Core: InMemoryRuleRegistry ───────────────────────────────────────
// The RuleRegistry the engine uses unless given another.
// ─────────────────────────────────────────────────────────────────────

/**
 * Keeps rules by alias in memory. withDefaults() registers the 52
 * built-in rules that take their configuration from parameters; the five
 * composite rules (each, callback, composite, conditional,
 * at_least_one_of) wrap other rules or closures, so they are built in
 * code and registered under an alias of the application's choice.
 *
 * Preconditions: an alias is a non-empty string, registered once.
 *
 * Thread-safety: register before sharing; resolution only reads.
 *
 * Side effects: register() adds to the registry.
 *
 * Example:
 *
 *     $registry = InMemoryRuleRegistry::withDefaults();
 *     $registry->register('cpf_or_cnpj', new AtLeastOneOfRule(new CpfRule(), new CnpjRule()));
 *
 * References: ADR-001; SPEC-002.
 */
final class InMemoryRuleRegistry implements RuleRegistry
{
    /** @var array<string, ValidationRule> */
    private array $rules = [];

    public static function withDefaults(): self
    {
        $registry = new self();

        foreach (self::defaults() as $alias => $rule) {
            $registry->register($alias, $rule);
        }

        return $registry;
    }

    #[\Override]
    public function register(string $alias, ValidationRule $rule): void
    {
        if ($alias === '') {
            throw InvalidRuleException::emptyAlias();
        }

        if (isset($this->rules[$alias])) {
            throw InvalidRuleException::duplicateAlias($alias);
        }

        $this->rules[$alias] = $rule;
    }

    #[\Override]
    public function resolve(string $alias): ValidationRule
    {
        return $this->rules[$alias] ?? throw InvalidRuleException::unknownAlias($alias);
    }

    #[\Override]
    public function has(string $alias): bool
    {
        return isset($this->rules[$alias]);
    }

    #[\Override]
    public function aliases(): array
    {
        return array_keys($this->rules);
    }

    /**
     * @return array<string, ValidationRule>
     */
    private static function defaults(): array
    {
        return [
            'required' => new RequiredRule(),
            'not_null' => new NotNullRule(),
            'nullable' => new NullableRule(),
            'choice' => new ChoiceRule(),
            'email' => new EmailRule(),
            'url' => new UrlRule(),
            'length' => new LengthRule(),
            'pattern' => new PatternRule(),
            'not_empty' => new NotEmptyRule(),
            'alpha' => new AlphaRule(),
            'alphanumeric' => new AlphanumericRule(),
            'uuid' => new UuidRule(),
            'ulid' => new UlidRule(),
            'ip' => new IpRule(),
            'json' => new JsonRule(),
            'hostname' => new HostnameRule(),
            'slug' => new SlugRule(),
            'starts_with' => new StartsWithRule(),
            'ends_with' => new EndsWithRule(),
            'integer' => new IntegerRule(),
            'decimal' => new DecimalRule(),
            'positive' => new PositiveRule(),
            'negative' => new NegativeRule(),
            'negative_or_zero' => new NegativeOrZeroRule(),
            'range' => new RangeRule(),
            'equal_to' => new EqualToRule(),
            'not_equal_to' => new NotEqualToRule(),
            'greater_than' => new GreaterThanRule(),
            'greater_than_or_equal' => new GreaterThanOrEqualRule(),
            'less_than' => new LessThanRule(),
            'less_than_or_equal' => new LessThanOrEqualRule(),
            'between' => new BetweenRule(),
            'date_format' => new DateFormatRule(),
            'date_before' => new DateBeforeRule(),
            'date_after' => new DateAfterRule(),
            'date_between' => new DateBetweenRule(),
            'type' => new TypeRule(),
            'instance_of' => new InstanceOfRule(),
            'count' => new CountRule(),
            'unique' => new UniqueElementsRule(),
            'key_exists' => new KeyExistsRule(),
            'same_as' => new SameAsRule(),
            'different_from' => new DifferentFromRule(),
            'required_with' => new RequiredWithRule(),
            'required_without' => new RequiredWithoutRule(),
            'luhn' => new LuhnRule(),
            'iban' => new IbanRule(),
            'bic' => new BicRule(),
            'cpf' => new CpfRule(),
            'cnpj' => new CnpjRule(),
            'cep' => new CepRule(),
            'pis' => new PisRule(),
        ];
    }
}
