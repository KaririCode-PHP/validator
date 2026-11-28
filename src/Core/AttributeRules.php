<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\Contract\Inspection\PropertyAttributeHandler;
use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;

// ── Core: AttributeRules ─────────────────────────────────────────────
// Collects #[Validate] and #[ValidationGroup] from an inspected object.
// ─────────────────────────────────────────────────────────────────────

/**
 * Receives, from a kariricode/contract PropertyInspector, every
 * #[Validate] and #[ValidationGroup] attribute of an object with the
 * value of its property, and turns them into the data and rule map the
 * engine validates. A property with #[ValidationGroup] belongs to the
 * groups it names; a property without one belongs to every group.
 *
 * Preconditions: one instance per validated object; attributes of other
 * classes are ignored.
 *
 * Thread-safety: mutable while the inspector fills it; not shared.
 *
 * Side effects: handleAttribute() records what it receives.
 *
 * Example:
 *
 *     $rules = new AttributeRules();
 *     $inspector->inspect(object: $dto, attributeClass: Validate::class, handler: $rules);
 *     $engine->validate($rules->values(null), $rules->fieldRules(null));
 *
 * References: kariricode/contract ADR-007 (Inspection); ADR-003.
 */
final class AttributeRules implements PropertyAttributeHandler
{
    /** @var array<string, mixed> */
    private array $values = [];

    /** @var array<string, list<mixed>> */
    private array $rules = [];

    /** @var array<string, list<string>> */
    private array $groups = [];

    #[\Override]
    public function handleAttribute(string $propertyName, object $attribute, mixed $value): void
    {
        if ($attribute instanceof ValidationGroup) {
            $this->groups[$propertyName] = $attribute->groups;
        }

        if ($attribute instanceof Validate) {
            $this->values += [$propertyName => $value];
            $this->rules[$propertyName] = [...$this->rules[$propertyName] ?? [], ...$attribute->rules];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function values(?string $group): array
    {
        return array_filter(array: $this->values, callback: fn(string $property): bool => $this->belongs($property, $group), mode: ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function fieldRules(?string $group): array
    {
        return array_filter(array: $this->rules, callback: fn(string $property): bool => $this->belongs($property, $group), mode: ARRAY_FILTER_USE_KEY);
    }

    private function belongs(string $property, ?string $group): bool
    {
        return $group === null || !isset($this->groups[$property]) || in_array(needle: $group, haystack: $this->groups[$property], strict: true);
    }
}
