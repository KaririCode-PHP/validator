<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\PropertyInspector\AttributeAnalyzer;
use KaririCode\PropertyInspector\Utility\PropertyInspector;
use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;
use KaririCode\Validator\Result\ValidationResult;

/**
 * Validates objects by reading #[Validate] attributes from properties.
 *
 * Uses kariricode/property-inspector for reflection caching and
 * attribute scanning — eliminates manual ReflectionClass loops.
 *
 * Supports #[ValidationGroup] filtering via an optional $group parameter.
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
 * @since   3.2.0 ARFA 1.3
 */
final readonly class AttributeValidator
{
    /**
     * Scans for both #[Validate] and #[ValidationGroup] on the same properties.
     * IS_INSTANCEOF ensures repeatable attributes are all captured.
     */
    private PropertyInspector $inspector;

    public function __construct(private ValidatorEngine $engine)
    {
        // Scan for Validate; ValidationGroup is handled by the handler
        // using a second AttributeAnalyzer pass via a combined inspector.
        // We use IS_INSTANCEOF so both attribute classes are returned.
        $this->inspector = new PropertyInspector(
            new AttributeAnalyzer(Validate::class),
        );
    }

    /**
     * Validates an object by introspecting #[Validate] and #[ValidationGroup] attributes.
     *
     * @param object      $object Object to validate
     * @param string|null $group  Validation group filter (null = all groups)
     */
    public function validate(object $object, ?string $group = null): ValidationResult
    {
        // We need to inspect BOTH Validate and ValidationGroup attributes.
        // The handler receives all instances; it handles ValidationGroup internally.
        $handler = new ValidateAttributeHandler($group);

        // Run a combined scan: Validate + ValidationGroup
        $validateHandler = $this->inspector->inspect($object, $handler);

        // For group filtering we need a second pass for ValidationGroup attributes
        if ($group !== null) {
            $groupInspector = new PropertyInspector(
                new AttributeAnalyzer(ValidationGroup::class),
            );
            $groupInspector->inspect($object, $validateHandler);
        }

        /** @var ValidateAttributeHandler $validateHandler */
        return $this->engine->validate(
            $validateHandler->getProcessedPropertyValues(),
            $validateHandler->getFieldRules(),
            $group,
        );
    }
}
