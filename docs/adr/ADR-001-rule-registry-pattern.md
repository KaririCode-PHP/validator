# ADR-001: Rule Registry Pattern

**Status:** Accepted
**Date:** 2026-03-06
**Component:** KaririCode\\Validator V3.1

## Context

The validator must support 52 built-in rules across 8 categories without coupling the engine
to concrete implementations. The engine must resolve rules by alias at runtime.

## Decision

Alias-based in-memory registry implementing `RuleRegistry`:

```php
interface RuleRegistry
{
    public function register(string $alias, ValidationRule $rule): void;
    public function resolve(string $alias): ValidationRule;
    public function has(string $alias): bool;
    public function aliases(): array;
}
```

`ValidatorServiceProvider` pre-registers all 52 built-in rules.

## Consequences

- Any rule can be registered — built-in or custom.
- Engine is fully decoupled from concrete rule classes.
