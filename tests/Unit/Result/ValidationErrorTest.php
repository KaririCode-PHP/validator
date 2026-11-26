<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Result;

use KaririCode\Validator\Result\ValidationError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationError::class)]
final class ValidationErrorTest extends TestCase
{
    public function testConstructSetsAllProperties(): void
    {
        $error = new ValidationError(
            field: 'email',
            errorCode: 'string.email',
            message: 'Invalid email.',
            context: ['max' => 255],
        );

        $this->assertSame('email', $error->field);
        $this->assertSame('string.email', $error->errorCode);
        $this->assertSame('Invalid email.', $error->message);
        $this->assertSame(['max' => 255], $error->context);
    }

    public function testConstructWithEmptyContext(): void
    {
        $error = new ValidationError('name', 'logic.required', 'Required.');

        $this->assertSame([], $error->context);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(ValidationError::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testDotNotationFieldName(): void
    {
        $error = new ValidationError(
            field: 'address.city',
            errorCode: 'string.not_empty',
            message: 'City required.',
        );

        $this->assertSame('address.city', $error->field);
    }
}
