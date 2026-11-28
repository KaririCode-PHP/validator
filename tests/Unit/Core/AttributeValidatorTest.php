<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;
use KaririCode\Validator\Core\AttributeRules;
use KaririCode\Validator\Core\AttributeValidator;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Tests\Support\ReflectionInspector;
use KaririCode\Validator\Tests\Support\SignUp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

// ── Unit: AttributeValidatorTest ─────────────────────────────────────
// #[Validate] and #[ValidationGroup] through the contract PropertyInspector.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(AttributeValidator::class)]
#[CoversClass(AttributeRules::class)]
#[CoversClass(Validate::class)]
#[CoversClass(ValidationGroup::class)]
final class AttributeValidatorTest extends TestCase
{
    #[Test]
    public function anObjectIsValidatedByTheAttributesOnItsProperties(): void
    {
        $inspector = new ReflectionInspector();
        $validator = new AttributeValidator(new ValidatorEngine(), $inspector);

        $result = $validator->validate(new SignUp(email: 'ana', password: 'short', passwordConfirmation: 'other', nickname: str_repeat('n', 21), backupEmails: ['x']));

        self::assertSame([
            'email' => ['The email must be a valid email address.'],
            'password' => ['The password must be at least 12 characters.'],
            'passwordConfirmation' => ['The passwordConfirmation must match password.'],
            'nickname' => ['The nickname must be at most 20 characters.'],
            'id' => ['The id field is required.', 'The id must be a valid UUID.'],
            'backupEmails' => ['Each item in backupEmails must be valid.'],
        ], $result->messages());
        self::assertSame([Validate::class], $inspector->inspected);
    }

    #[Test]
    public function aGroupValidatesItsPropertiesAndThoseWithoutGroup(): void
    {
        $inspector = new ReflectionInspector();
        $validator = new AttributeValidator(new ValidatorEngine(), $inspector);
        $signUp = new SignUp(email: 'ana@example.com', password: 'correct horse battery', passwordConfirmation: 'correct horse battery', backupEmails: ['x']);

        self::assertSame(['backupEmails'], $validator->validate($signUp, 'create')->failedFields());
        self::assertSame(['id', 'backupEmails'], $validator->validate($signUp, 'update')->failedFields());
        self::assertSame([], $validator->validate($signUp, 'archive')->failedFields());
        self::assertSame([Validate::class, ValidationGroup::class, Validate::class, ValidationGroup::class, Validate::class, ValidationGroup::class], $inspector->inspected);
    }

    #[Test]
    public function theCollectorIgnoresOtherAttributesAndJoinsRepeatedOnes(): void
    {
        $rules = new AttributeRules();
        $rules->handleAttribute('a', new Validate('required'), 'x');
        $rules->handleAttribute('a', new Validate(['length', ['max' => 3]]), 'x');
        $rules->handleAttribute('a', new stdClass(), 'ignored');
        $rules->handleAttribute('b', new ValidationGroup('admin'), 'y');
        $rules->handleAttribute('b', new Validate('email'), 'y');

        self::assertSame(['a' => ['required', ['length', ['max' => 3]]], 'b' => ['email']], $rules->fieldRules(null));
        self::assertSame(['a' => 'x', 'b' => 'y'], $rules->values(null));
        self::assertSame(['a' => 'x'], $rules->values('public'));
        self::assertSame(['a' => 'x', 'b' => 'y'], $rules->values('admin'));
        self::assertSame(['a' => ['required', ['length', ['max' => 3]]]], $rules->fieldRules('public'));
    }

    #[Test]
    public function validateTakesPositionalRulesOnly(): void
    {
        self::assertSame(['required', ['length', ['min' => 1]]], (new Validate('required', ['length', ['min' => 1]]))->rules);
        self::assertSame(['create', 'update'], (new ValidationGroup('create', 'update'))->groups);
        self::assertSame(['create'], (new ValidationGroup(first: 'create'))->groups);

        try {
            new Validate('required', mode: 'strict', level: 'high');
            self::fail('Named arguments were accepted.');
        } catch (InvalidRuleException $exception) {
            self::assertSame(ValidatorCode::NamedAttributeArguments, $exception->errorCode);
            self::assertSame("[VAL_3206] #[Validate] takes no named arguments (mode, level); pass parameters as ['alias', ['parameter' => value]].", $exception->getMessage());
        }
    }
}
