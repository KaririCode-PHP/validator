<?php

declare(strict_types=1);

namespace KaririCode\Validator\Attribute;

use Attribute;

// ── Attribute: ValidationGroup ───────────────────────────────────────
// Places a property in validation groups.
// ─────────────────────────────────────────────────────────────────────

/**
 * Names the groups a property's #[Validate] rules belong to, so one DTO
 * serves several operations: validating with group 'create' skips the
 * properties marked only 'update'. A property without this attribute is
 * validated in every group.
 *
 * Preconditions: none.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     #[Validate('required', 'uuid')]
 *     #[ValidationGroup('update')]
 *     public ?string $id = null;
 *
 * References: SPEC-001 §7.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ValidationGroup
{
    /** @var list<string> */
    public array $groups;

    public function __construct(string ...$groups)
    {
        $this->groups = array_values($groups);
    }
}
