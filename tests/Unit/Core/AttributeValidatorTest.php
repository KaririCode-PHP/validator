<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;
use KaririCode\Validator\Provider\ValidatorServiceProvider;
use PHPUnit\Framework\TestCase;

final class AttributeValidatorTest extends TestCase
{
    public function testValidatesObjectFromAttributes(): void
    {
        $validator = (new ValidatorServiceProvider())->createAttributeValidator();
        $dto = new class ('invalid', 'ab') {
            public function __construct(
                #[Validate('required', 'email')]
                public string $email,
                #[Validate('required', ['length', ['min' => 8]])]
                public string $password,
            ) {
            }
        };

        $result = $validator->validate($dto);
        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->errorsFor('email'));
        $this->assertNotEmpty($result->errorsFor('password'));
    }

    public function testValidObjectPassesValidation(): void
    {
        $validator = (new ValidatorServiceProvider())->createAttributeValidator();
        $dto = new class ('user@test.com', 'securepassword') {
            public function __construct(
                #[Validate('required', 'email')]
                public string $email,
                #[Validate('required', ['length', ['min' => 8]])]
                public string $password,
            ) {
            }
        };

        $result = $validator->validate($dto);
        $this->assertTrue($result->isValid());
    }

    public function testGroupFilteringSkipsNonMatchingProperties(): void
    {
        $validator = (new ValidatorServiceProvider())->createAttributeValidator();
        $dto = new class ('user@test.com', '') {
            public function __construct(
                #[Validate('required', 'email')]
                #[ValidationGroup('create', 'update')]
                public string $email,
                #[Validate('required', ['length', ['min' => 8]])]
                #[ValidationGroup('create')]
                public string $password,
            ) {
            }
        };

        // 'update' group — password should be skipped
        $result = $validator->validate($dto, 'update');
        $this->assertTrue($result->isValid());
    }

    public function testCreateGroupValidatesBothFields(): void
    {
        $validator = (new ValidatorServiceProvider())->createAttributeValidator();
        $dto = new class ('user@test.com', '') {
            public function __construct(
                #[Validate('required', 'email')]
                #[ValidationGroup('create', 'update')]
                public string $email,
                #[Validate('required', ['length', ['min' => 8]])]
                #[ValidationGroup('create')]
                public string $password,
            ) {
            }
        };

        $result = $validator->validate($dto, 'create');
        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->errorsFor('password'));
    }
}
