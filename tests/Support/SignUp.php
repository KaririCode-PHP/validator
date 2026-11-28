<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Support;

use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;
use KaririCode\Validator\Rule\Collection\EachRule;
use KaririCode\Validator\Rule\String\EmailRule;

// ── Support: SignUp ──────────────────────────────────────────────────
// A DTO with #[Validate] and #[ValidationGroup], as an application writes one.
// ─────────────────────────────────────────────────────────────────────

/** A sign-up form: required fields, cross-field rules, groups, a rule instance. */
final readonly class SignUp
{
    /**
     * @param list<string> $backupEmails
     */
    public function __construct(
        #[Validate('required', 'email')]
        public string $email,
        #[Validate('required')]
        #[Validate(['length', ['min' => 12]])]
        public string $password,
        #[Validate(['same_as', ['other' => 'password']])]
        public string $passwordConfirmation,
        #[Validate('nullable', ['length', ['max' => 20]])]
        public ?string $nickname = null,
        #[Validate('required', 'uuid')]
        #[ValidationGroup('update')]
        public ?string $id = null,
        #[Validate(new EachRule(new EmailRule()))]
        #[ValidationGroup('create', 'update')]
        public array $backupEmails = [],
        public string $notValidated = '',
    ) {}
}
