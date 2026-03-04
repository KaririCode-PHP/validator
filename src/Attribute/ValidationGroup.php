<?php

declare(strict_types=1);

namespace KaririCode\Validator\Attribute;

/**
 * Defines validation groups for selective rule execution.
 *
 * ```php
 * final readonly class UpdateUserRequest
 * {
 *     #[Validate('required', 'email')]
 *     #[ValidationGroup('create', 'update')]
 *     public string $email;
 *
 *     #[Validate('required', ['length', ['min' => 8]])]
 *     #[ValidationGroup('create')]
 *     public string $password;
 * }
 * ```
 *
 * @package KaririCode\Validator\Attribute
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class ValidationGroup
{
    /** @var array<string> */
    public array $groups;

    public function __construct(string ...$groups)
    {
        $this->groups = $groups;
    }
}
