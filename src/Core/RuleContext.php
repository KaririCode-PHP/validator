<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\Validator\Contract\ValidationContext;
use KaririCode\Validator\Exception\InvalidRuleException;

// ── Core: RuleContext ────────────────────────────────────────────────
// The ValidationContext the engine hands to each rule.
// ─────────────────────────────────────────────────────────────────────

/**
 * Carries the data under validation, the field being checked, the group
 * and the parameters of the rule being applied, and checks parameter
 * types for the rules. The engine builds one per validation and derives
 * a copy per field and per rule.
 *
 * Preconditions: data is the whole input, so cross-field rules can read
 * other fields.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none; the typed readers throw InvalidRuleException for a
 * parameter that is missing or of the wrong type.
 *
 * Example:
 *
 *     $context = (new RuleContext(data: ['age' => 20]))->withField('age')->withParameters(['min' => 18]);
 *     $context->intParameter('min'); // 18
 *
 * References: ADR-001; SPEC-001 §4.
 */
final readonly class RuleContext implements ValidationContext
{
    /**
     * @param array<array-key, mixed> $data
     * @param array<string, mixed>    $parameters
     */
    public function __construct(
        public array $data = [],
        public string $field = '',
        public array $parameters = [],
        public ?string $group = null,
    ) {}

    #[\Override]
    public function withField(string $field): static
    {
        return new self(data: $this->data, field: $field, parameters: $this->parameters, group: $this->group);
    }

    #[\Override]
    public function withParameters(array $parameters): static
    {
        return new self(data: $this->data, field: $this->field, parameters: [...$this->parameters, ...$parameters], group: $this->group);
    }

    #[\Override]
    public function hasParameter(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    #[\Override]
    public function parameter(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->parameters) ? $this->parameters[$name] : $default;
    }

    #[\Override]
    public function intParameter(string $name, ?int $default = null): int
    {
        /** @var mixed $value */
        $value = $this->required($name, $default);

        return is_int($value) ? $value : throw $this->invalid(name: $name, expected: 'an integer', value: $value);
    }

    #[\Override]
    public function numberParameter(string $name, int|float|null $default = null): int|float
    {
        /** @var mixed $value */
        $value = $this->required($name, $default);

        return is_int($value) || (is_float($value) && is_finite($value)) ? $value : throw $this->invalid(name: $name, expected: 'a finite number', value: $value);
    }

    #[\Override]
    public function stringParameter(string $name, ?string $default = null): string
    {
        /** @var mixed $value */
        $value = $this->required($name, $default);

        return is_string($value) ? $value : throw $this->invalid(name: $name, expected: 'a string', value: $value);
    }

    #[\Override]
    public function arrayParameter(string $name): array
    {
        /** @var mixed $value */
        $value = $this->required($name, null);

        return is_array($value) ? $value : throw $this->invalid(name: $name, expected: 'an array', value: $value);
    }

    #[\Override]
    public function hasField(string $path): bool
    {
        return DataPath::exists($this->data, $path);
    }

    #[\Override]
    public function fieldValue(string $path): mixed
    {
        return DataPath::value($this->data, $path);
    }

    private function required(string $name, mixed $default): mixed
    {
        if (array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        return $default ?? throw InvalidRuleException::missingParameter(field: $this->field, parameter: $name);
    }

    private function invalid(string $name, string $expected, mixed $value): InvalidRuleException
    {
        return InvalidRuleException::invalidParameter(field: $this->field, parameter: $name, expected: $expected, given: get_debug_type($value));
    }
}
