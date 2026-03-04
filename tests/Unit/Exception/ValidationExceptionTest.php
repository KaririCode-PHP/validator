<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Exception;

use KaririCode\Validator\Exception\ValidationException;
use KaririCode\Validator\Result\ValidationResult;
use PHPUnit\Framework\TestCase;

final class ValidationExceptionTest extends TestCase
{
    public function testCarriesValidationResult(): void
    {
        $result = new ValidationResult();
        $result->add('email', 'string.email', 'Invalid.');

        $exception = new ValidationException($result);

        $this->assertSame($result, $exception->result);
        $this->assertSame(422, $exception->getCode());
        $this->assertSame('Validation failed.', $exception->getMessage());
    }

    public function testFromResultFactoryMethod(): void
    {
        $result = new ValidationResult();
        $result->add('email', 'string.email', 'Invalid email.');
        $result->add('name', 'logic.required', 'Required.');

        $exception = ValidationException::fromResult($result);

        $this->assertSame($result, $exception->result);
        $this->assertStringContainsString('2 validation error(s)', $exception->getMessage());
        $this->assertStringContainsString('email', $exception->getMessage());
        $this->assertStringContainsString('name', $exception->getMessage());
    }

    public function testCustomMessageAndCode(): void
    {
        $result = new ValidationResult();
        $previous = new \RuntimeException('root cause');

        $exception = new ValidationException($result, 'Custom.', 400, $previous);

        $this->assertSame('Custom.', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsRuntimeException(): void
    {
        $exception = new ValidationException(new ValidationResult());
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
