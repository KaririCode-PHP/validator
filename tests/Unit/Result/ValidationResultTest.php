<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Result;

use KaririCode\Validator\Result\ValidationError;
use KaririCode\Validator\Result\ValidationResult;
use KaririCode\Validator\Result\Violation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

// ── Unit: ValidationResultTest ───────────────────────────────────────
// The result and its parts: queries, combination, immutability.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(ValidationResult::class)]
#[CoversClass(ValidationError::class)]
#[CoversClass(Violation::class)]
final class ValidationResultTest extends TestCase
{
    #[Test]
    public function anEmptyResultIsValid(): void
    {
        $result = new ValidationResult();

        self::assertTrue($result->isValid());
        self::assertSame(0, $result->errorCount());
        self::assertSame([], $result->errors);
        self::assertSame([], $result->failedFields());
        self::assertSame([], $result->messages());
        self::assertNull($result->firstErrorFor('email'));
    }

    #[Test]
    public function errorsAreQueriedByField(): void
    {
        $email = new ValidationError('email', 'string.email', 'Bad email.', ['field' => 'email']);
        $short = new ValidationError('password', 'string.length_min', 'Too short.');
        $weak = new ValidationError('password', 'custom.weak', 'Too weak.');
        $result = new ValidationResult($email, $short, $weak);

        self::assertFalse($result->isValid());
        self::assertSame(3, $result->errorCount());
        self::assertSame([$short, $weak], $result->errorsFor('password'));
        self::assertSame([$email], $result->errorsFor('email'));
        self::assertSame($short, $result->firstErrorFor('password'));
        self::assertSame(['email', 'password'], $result->failedFields());
        self::assertSame(['email' => ['Bad email.'], 'password' => ['Too short.', 'Too weak.']], $result->messages());
        self::assertSame(['field' => 'email'], $email->parameters);
        self::assertSame([], $short->parameters);
    }

    #[Test]
    public function resultsCombineIntoNewResults(): void
    {
        $first = new ValidationResult(new ValidationError('a', 'x', 'A.'));
        $second = new ValidationResult(new ValidationError('b', 'y', 'B.'));
        $extra = new ValidationError('c', 'z', 'C.');

        self::assertSame(['a', 'b'], $first->merge($second)->failedFields());
        self::assertSame(['a', 'c'], $first->with($extra)->failedFields());
        self::assertSame(['a'], $first->failedFields());
        self::assertSame([0, 1], array_keys((new ValidationResult(...['k1' => $extra, 'k2' => $extra]))->errors));
    }

    #[Test]
    public function aViolationCarriesItsCodeMessageAndPlaceholders(): void
    {
        $violation = new Violation(code: 'numeric.decimal', message: 'At most {places}.', parameters: ['places' => 2]);

        self::assertSame(['numeric.decimal', 'At most {places}.', ['places' => 2]], [$violation->code, $violation->message, $violation->parameters]);
        self::assertSame([], (new Violation('c', 'm'))->parameters);
    }
}
