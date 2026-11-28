<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Support;

use KaririCode\Contract\Inspection\PropertyAttributeHandler;
use KaririCode\Contract\Inspection\PropertyInspector;
use ReflectionObject;

// ── Support: ReflectionInspector ─────────────────────────────────────
// The kariricode/contract PropertyInspector, implemented for the tests.
// ─────────────────────────────────────────────────────────────────────

/**
 * Walks an object's properties by reflection and hands each instance of
 * the attribute class to the handler with the property's value, as
 * kariricode/property-inspector's ReflectionPropertyInspector does; an
 * uninitialized property reads as null here.
 */
final class ReflectionInspector implements PropertyInspector
{
    /** @var list<string> attribute classes inspected, in order */
    public private(set) array $inspected = [];

    public function inspect(object $object, string $attributeClass, PropertyAttributeHandler $handler): void
    {
        $this->inspected[] = $attributeClass;

        foreach ((new ReflectionObject($object))->getProperties() as $property) {
            $value = $property->isInitialized($object) ? $property->getValue($object) : null;

            foreach ($property->getAttributes($attributeClass) as $attribute) {
                $handler->handleAttribute($property->getName(), $attribute->newInstance(), $value);
            }
        }
    }
}
