<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\PropertyInspector\Contract\PropertyAttributeHandler;
use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;

/**
 * Collects #[Validate] rule definitions from each property, with optional
 * group filtering via #[ValidationGroup] attributes.
 *
 * @package KaririCode\Validator\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.2.0 ARFA 1.3
 */
final class ValidateAttributeHandler implements PropertyAttributeHandler
{
    /** @var array<string, mixed> */
    private array $data = [];

    /** @var array<string, list<mixed>> */
    private array $fieldRules = [];

    /** @var array<string, bool> Tracks which properties have had their group check processed. */
    private array $groupChecked = [];

    public function __construct(private readonly ?string $group = null)
    {
    }

    #[\Override]
    public function handleAttribute(string $propertyName, object $attribute, mixed $value): mixed
    {
        // Handle group filtering via #[ValidationGroup]
        if ($attribute instanceof ValidationGroup) {
            if ($this->group !== null) {
                // If property is not in the requested group, mark it to be skipped
                if (! \in_array($this->group, $attribute->groups, strict: true)) {
                    $this->groupChecked[$propertyName] = false;
                } else {
                    $this->groupChecked[$propertyName] ??= true;
                }
            }

            return null;
        }

        if (! $attribute instanceof Validate) {
            return null;
        }

        $this->data[$propertyName] = $value;

        if (! isset($this->fieldRules[$propertyName])) {
            $this->fieldRules[$propertyName] = [];
        }

        $this->fieldRules[$propertyName] = array_values([
            ...$this->fieldRules[$propertyName],
            ...$attribute->rules,
        ]);

        return null;
    }

    #[\Override]
    public function getProcessedPropertyValues(): array
    {
        return $this->filterByGroup($this->data);
    }

    #[\Override]
    public function getProcessingResultMessages(): array
    {
        return [];
    }

    #[\Override]
    public function getProcessingResultErrors(): array
    {
        return [];
    }

    /** @return array<string, list<mixed>> */
    public function getFieldRules(): array
    {
        return $this->filterByGroup($this->fieldRules);
    }

    /**
     * @template T
     * @param array<string, T> $map
     * @return array<string, T>
     */
    private function filterByGroup(array $map): array
    {
        if ($this->group === null) {
            return $map;
        }

        return array_filter(
            $map,
            fn (string $property) => $this->groupChecked[$property] ?? true,
            ARRAY_FILTER_USE_KEY,
        );
    }
}
