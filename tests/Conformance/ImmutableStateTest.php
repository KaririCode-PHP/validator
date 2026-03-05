<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Conformance;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Provider\ValidatorServiceProvider;
use KaririCode\Validator\Result\ValidationError;
use PHPUnit\Framework\TestCase;

/**
 * ARFA 1.3 P1 — Immutable State Transformation.
 *
 * ∀s ∈ States, ∀t ∈ Transformations: t(s) → s' where s ≠ s' ∧ s.immutable = true
 */
final class ImmutableStateTest extends TestCase
{
    public function testValidationContextWithFieldReturnsNewInstance(): void
    {
        $original = ValidationContextImpl::create(['name' => 'Walmir']);
        $derived = $original->withField('name');

        $this->assertNotSame($original, $derived);
        $this->assertSame('', $original->getFieldName());
        $this->assertSame('name', $derived->getFieldName());
    }

    public function testValidationContextWithParametersReturnsNewInstance(): void
    {
        $original = ValidationContextImpl::create([])->withField('age');
        $derived = $original->withParameters(['min' => 18]);

        $this->assertNotSame($original, $derived);
        $this->assertSame([], $original->getParameters());
        $this->assertSame(['min' => 18], $derived->getParameters());
    }

    public function testValidationErrorIsReadonly(): void
    {
        $ref = new \ReflectionClass(ValidationError::class);
        $this->assertTrue($ref->isReadOnly());
    }

    public function testValidationContextImplIsReadonly(): void
    {
        $ref = new \ReflectionClass(ValidationContextImpl::class);
        $this->assertTrue($ref->isReadOnly());
    }

    public function testInputDataIsNotMutatedByEngine(): void
    {
        $engine = new ValidatorServiceProvider()->createEngine();
        $data = ['email' => 'user@test.com', 'name' => 'Test'];
        $snapshot = $data;

        $engine->validate($data, [
            'email' => ['required', 'email'],
            'name' => ['required'],
        ]);

        $this->assertSame($snapshot, $data, 'Engine must not mutate input data (P1).');
    }
}
