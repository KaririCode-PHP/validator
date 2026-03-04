<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;
use KaririCode\Validator\Result\ValidationResult;

/**
 * Validates objects by reading #[Validate] attributes from properties.
 *
 * Bridges PHP 8.4 attributes to the ValidatorEngine, enabling declarative
 * validation on DTOs and domain objects.
 *
 * ```php
 * final readonly class CreateUserRequest
 * {
 *     public function __construct(
 *         #[Validate('required', 'email')]
 *         #[ValidationGroup('create', 'update')]
 *         public string $email,
 *
 *         #[Validate('required', ['length', ['min' => 8]])]
 *         #[ValidationGroup('create')]
 *         public string $password,
 *     ) {}
 * }
 *
 * $validator = new AttributeValidator($engine);
 * $result = $validator->validate(new CreateUserRequest(...));
 * ```
 *
 * @package KaririCode\Validator\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class AttributeValidator
{
    public function __construct(
        private ValidatorEngine $engine,
    ) {
    }

    /**
     * Validates an object by introspecting #[Validate] attributes.
     *
     * @param object      $object Object to validate
     * @param string|null $group  Validation group filter (null = all)
     */
    public function validate(object $object, ?string $group = null): ValidationResult
    {
        $reflection = new \ReflectionClass($object);
        $fieldRules = [];
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $validateAttributes = $property->getAttributes(Validate::class);

            if ($validateAttributes === []) {
                continue;
            }

            // Group filtering
            if ($group !== null && !$this->propertyBelongsToGroup($property, $group)) {
                continue;
            }

            $propertyName = $property->getName();
            $property->setAccessible(true);
            $data[$propertyName] = $property->isInitialized($object)
                ? $property->getValue($object)
                : null;

            $rules = [];
            foreach ($validateAttributes as $attribute) {
                /** @var Validate $validate */
                $validate = $attribute->newInstance();
                $rules = array_merge($rules, $validate->rules);
            }

            $fieldRules[$propertyName] = $rules;
        }

        return $this->engine->validate($data, $fieldRules, $group);
    }

    /**
     * Checks if a property belongs to the given validation group.
     */
    private function propertyBelongsToGroup(\ReflectionProperty $property, string $group): bool
    {
        $groupAttributes = $property->getAttributes(ValidationGroup::class);

        // No group attribute = belongs to all groups
        if ($groupAttributes === []) {
            return true;
        }

        /** @var ValidationGroup $groupAttr */
        $groupAttr = $groupAttributes[0]->newInstance();

        return in_array($group, $groupAttr->groups, strict: true);
    }
}
