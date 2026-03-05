<?php

declare(strict_types=1);

namespace KaririCode\Validator\Attribute;

/**
 * PHP 8.4 attribute for declarative property validation.
 *
 * Applied to class properties to define validation rules inline.
 * The ValidatorEngine can inspect these via PropertyInspector
 * for attribute-driven validation.
 *
 * ```php
 * final readonly class CreateUserRequest
 * {
 *     public function __construct(
 *         #[Validate('required', 'email', ['length', ['max' => 255]])]
 *         public string $email,
 *
 *         #[Validate('required', ['length', ['min' => 8, 'max' => 128]])]
 *         public string $password,
 *
 *         #[Validate(['range', ['min' => 18, 'max' => 150]])]
 *         public ?int $age = null,
 *     ) {}
 * }
 * ```
 *
 * @package KaririCode\Validator\Attribute
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
final readonly class Validate
{
    /** @var array<array-key, string|array<mixed>> */
    public array $rules;

    /**
     * @param string|array<mixed> ...$rules Rule aliases or [alias, params] tuples
     */
    public function __construct(string|array ...$rules)
    {
        $this->rules = $rules;
    }
}
