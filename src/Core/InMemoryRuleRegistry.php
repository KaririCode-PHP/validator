<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\Validator\Contract\RuleRegistry;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;

/**
 * In-memory rule registry with alias-based resolution.
 *
 * Thread-safe for read operations after initial registration phase.
 * Rules are registered at bootstrap and resolved during validation.
 *
 * ## ARFA 1.3 Alignment
 *
 * P4 (Protocol Agnostic): Rules resolved by alias, not by FQCN.
 *
 * @package KaririCode\Validator\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class InMemoryRuleRegistry implements RuleRegistry
{
    /** @var array<string, ValidationRule> */
    private array $rules = [];

    public function register(string $alias, ValidationRule $rule): void
    {
        if (isset($this->rules[$alias])) {
            throw InvalidRuleException::duplicateAlias($alias);
        }

        $this->rules[$alias] = $rule;
    }

    public function resolve(string $alias): ValidationRule
    {
        return $this->rules[$alias]
            ?? throw InvalidRuleException::unknownAlias($alias);
    }

    public function has(string $alias): bool
    {
        return isset($this->rules[$alias]);
    }

    public function aliases(): array
    {
        return array_keys($this->rules);
    }
}
