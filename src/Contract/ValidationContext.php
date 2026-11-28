<?php

declare(strict_types=1);

namespace KaririCode\Validator\Contract;

// ── Contract: ValidationContext ──────────────────────────────────────
// What a rule may read besides the value: the field, the data, its parameters.
// ─────────────────────────────────────────────────────────────────────

/**
 * Gives a rule the name of the field it checks, every field of the data
 * (for cross-field rules), the validation group, and the parameters of
 * this use of the rule, with readers that check a parameter's type. A
 * parameter that is missing or of the wrong type is the application's
 * mistake: the typed readers throw InvalidRuleException instead of
 * letting a rule cast it.
 *
 * Preconditions: field paths use dots for nesting ("address.city"); a
 * key that contains dots itself is matched first.
 *
 * Thread-safety: immutable; the with methods return copies.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $min = $context->intParameter('min', 0);
 *     $other = $context->fieldValue($context->stringParameter('other'));
 *
 * References: ADR-001; SPEC-001 §4.
 */
interface ValidationContext
{
    public string $field { get; }

    /** @var array<array-key, mixed> */
    public array $data { get; }

    /** @var array<string, mixed> */
    public array $parameters { get; }

    public ?string $group { get; }

    public function withField(string $field): static;

    /**
     * @param array<string, mixed> $parameters
     */
    public function withParameters(array $parameters): static;

    public function hasParameter(string $name): bool;

    public function parameter(string $name, mixed $default = null): mixed;

    public function intParameter(string $name, ?int $default = null): int;

    public function numberParameter(string $name, int|float|null $default = null): int|float;

    public function stringParameter(string $name, ?string $default = null): string;

    /**
     * @return array<array-key, mixed>
     */
    public function arrayParameter(string $name): array;

    public function hasField(string $path): bool;

    public function fieldValue(string $path): mixed;
}
