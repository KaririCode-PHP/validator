<?php

declare(strict_types=1);

namespace KaririCode\Validator\Contract;

// ── Contract: RuleRegistry ───────────────────────────────────────────
// Rules by alias, so definitions can name them.
// ─────────────────────────────────────────────────────────────────────

/**
 * Maps the aliases rule definitions use ('email', 'length') to rule
 * instances. Registration is part of setting the engine up; resolving
 * happens on every validation.
 *
 * Preconditions: an alias is registered once.
 *
 * Thread-safety: implementations decide; register before sharing.
 *
 * Side effects: register() changes the registry.
 *
 * Example:
 *
 *     $registry->register('even', new CallbackRule(static fn (mixed $v): bool => is_int($v) && $v % 2 === 0));
 *     $rule = $registry->resolve('even');
 *
 * References: ADR-001; SPEC-002.
 */
interface RuleRegistry
{
    public function register(string $alias, ValidationRule $rule): void;

    public function resolve(string $alias): ValidationRule;

    public function has(string $alias): bool;

    /**
     * @return list<string>
     */
    public function aliases(): array;
}
