<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Result;

use KaririCode\Validator\Result\ValidationError;
use KaririCode\Validator\Result\ValidationResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationResult::class)]
#[CoversClass(ValidationError::class)]
final class ValidationResultTest extends TestCase
{
    private ValidationResult $result;

    protected function setUp(): void
    {
        $this->result = new ValidationResult();
    }

    public function testIsValidWhenEmpty(): void
    {
        $this->assertTrue($this->result->isValid());
    }

    public function testIsInvalidAfterAddError(): void
    {
        $this->result->addError(
            new ValidationError('email', 'string.email', 'Invalid.'),
        );

        $this->assertFalse($this->result->isValid());
    }

    public function testAddFluentApi(): void
    {
        $returned = $this->result->add('name', 'logic.required', 'Required.');

        $this->assertSame($this->result, $returned);
        $this->assertFalse($this->result->isValid());
    }

    public function testGetErrorsReturnsAllErrors(): void
    {
        $this->result->add('email', 'string.email', 'Invalid email.');
        $this->result->add('name', 'logic.required', 'Required.');
        $this->result->add('email', 'string.length', 'Too long.');

        $this->assertCount(3, $this->result->getErrors());
    }

    public function testErrorsForField(): void
    {
        $this->result->add('email', 'string.email', 'Invalid.');
        $this->result->add('name', 'logic.required', 'Required.');
        $this->result->add('email', 'string.length', 'Too long.');

        $emailErrors = $this->result->errorsFor('email');
        $this->assertCount(2, $emailErrors);
        $this->assertSame('string.email', $emailErrors[0]->errorCode);
        $this->assertSame('string.length', $emailErrors[1]->errorCode);
    }

    public function testErrorsForFieldReturnsEmptyWhenNoErrors(): void
    {
        $this->result->add('name', 'logic.required', 'Required.');

        $this->assertSame([], $this->result->errorsFor('email'));
    }

    public function testFirstErrorForField(): void
    {
        $this->result->add('email', 'string.email', 'Invalid.');
        $this->result->add('email', 'string.length', 'Too long.');

        $first = $this->result->firstErrorFor('email');
        $this->assertNotNull($first);
        $this->assertSame('string.email', $first->errorCode);
    }

    public function testFirstErrorForFieldReturnsNullWhenNoErrors(): void
    {
        $this->assertNull($this->result->firstErrorFor('missing'));
    }

    public function testFailedFields(): void
    {
        $this->result->add('email', 'string.email', 'Invalid.');
        $this->result->add('name', 'logic.required', 'Required.');
        $this->result->add('email', 'string.length', 'Too long.');

        $fields = $this->result->failedFields();
        $this->assertCount(2, $fields);
        $this->assertContains('email', $fields);
        $this->assertContains('name', $fields);
    }

    public function testFailedFieldsReturnsEmptyWhenValid(): void
    {
        $this->assertSame([], $this->result->failedFields());
    }

    public function testErrorCount(): void
    {
        $this->assertSame(0, $this->result->errorCount());

        $this->result->add('a', 'code', 'msg');
        $this->result->add('b', 'code', 'msg');

        $this->assertSame(2, $this->result->errorCount());
    }

    public function testMerge(): void
    {
        $this->result->add('email', 'string.email', 'Invalid.');

        $other = new ValidationResult();
        $other->add('name', 'logic.required', 'Required.');
        $other->add('age', 'numeric.positive', 'Must be positive.');

        $returned = $this->result->merge($other);

        $this->assertSame($this->result, $returned);
        $this->assertSame(3, $this->result->errorCount());
        $this->assertFalse($this->result->isValid());
    }

    public function testMergeWithEmptyResult(): void
    {
        $this->result->add('email', 'string.email', 'Invalid.');
        $this->result->merge(new ValidationResult());

        $this->assertSame(1, $this->result->errorCount());
    }

    public function testMergeEmptyIntoEmpty(): void
    {
        $this->result->merge(new ValidationResult());

        $this->assertTrue($this->result->isValid());
        $this->assertSame(0, $this->result->errorCount());
    }

    public function testAddErrorWithContext(): void
    {
        $this->result->add('age', 'numeric.range', 'Out of range.', ['min' => 0, 'max' => 150]);

        $error = $this->result->getErrors()[0];
        $this->assertSame(['min' => 0, 'max' => 150], $error->context);
    }
}
