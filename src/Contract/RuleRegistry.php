<?php

declare(strict_types=1);

namespace KaririCode\Validator\Contract;

use KaririCode\Validator\Exception\InvalidRuleException;

/**
 * Registry for validation rules, enabling name-based resolution.
 *
 * Decouples rule instantiation from usage — validators reference rules
 * by alias (e.g. `email`, `required`, `range`) instead of FQCN. This
 * enables runtime rule discovery, attribute-driven validation, and
 * framework-level rule overrides.
 *
 * ## ARFA 1.3 Alignment
 *
 * P4 (Protocol Agnostic): Rules registered by name, resolved at runtime.
 *
 * @package KaririCode\Validator\Contract
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
interface RuleRegistry
{
    /**
     * Registers a rule under the given alias.
     *
     * @param string         $alias Unique name (e.g. 'email', 'cnpj')
     * @param ValidationRule $rule  Rule instance
     *
     * @throws InvalidRuleException On duplicate alias
     */
    public function register(string $alias, ValidationRule $rule): void;

    /**
     * Resolves a rule by alias.
     *
     * @throws InvalidRuleException If alias not found
     */
    public function resolve(string $alias): ValidationRule;

    /**
     * Checks whether an alias is registered.
     */
    public function has(string $alias): bool;

    /**
     * Returns all registered aliases.
     *
     * @return array<string>
     */
    public function aliases(): array;
}
