<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\Contract\Inspection\PropertyInspector;
use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;
use KaririCode\Validator\Result\ValidationResult;

// ── Core: AttributeValidator ─────────────────────────────────────────
// Validates an object by the #[Validate] attributes on its properties.
// ─────────────────────────────────────────────────────────────────────

/**
 * Reads an object's #[Validate] attributes through a kariricode/contract
 * PropertyInspector (kariricode/property-inspector 3 implements it) and
 * validates the property values with the engine. With a group, only the
 * properties in that group are validated. Cross-field rules see the
 * properties that carry #[Validate].
 *
 * Preconditions: the inspector decides what an unreadable property means;
 * kariricode/property-inspector's ReflectionPropertyInspector throws for
 * an uninitialized one.
 *
 * Thread-safety: immutable; one AttributeRules per call.
 *
 * Side effects: none beyond the inspector's reads and the engine's
 * observer.
 *
 * Example:
 *
 *     $validator = new AttributeValidator(new ValidatorEngine(), new ReflectionPropertyInspector());
 *     $result = $validator->validate(new SignUp(email: 'ana@example.com', password: 'secret'));
 *
 * References: kariricode/property-inspector ADR-008 and ADR-009; ADR-003.
 */
final readonly class AttributeValidator
{
    public function __construct(
        private ValidatorEngine $engine,
        private PropertyInspector $inspector,
    ) {}

    public function validate(object $object, ?string $group = null): ValidationResult
    {
        $rules = new AttributeRules();
        $this->inspector->inspect(object: $object, attributeClass: Validate::class, handler: $rules);

        if ($group !== null) {
            $this->inspector->inspect(object: $object, attributeClass: ValidationGroup::class, handler: $rules);
        }

        return $this->engine->validate(data: $rules->values($group), fieldRules: $rules->fieldRules($group), group: $group);
    }
}
