<?php

declare(strict_types=1);

namespace KaririCode\Validator\Attribute;

use Attribute;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Exception\InvalidRuleException;

// ── Attribute: Validate ──────────────────────────────────────────────
// Declares the rules of a property.
// ─────────────────────────────────────────────────────────────────────

/**
 * Lists, on a property, the rule definitions AttributeValidator applies
 * to its value, in the same forms the engine accepts: an alias, a rule
 * instance, or [alias or rule, parameters]. Repeatable; the rules of
 * every #[Validate] on a property run in order.
 *
 * Preconditions: rules are positional; named arguments would be lost
 * between the attribute and the engine, so they throw
 * InvalidRuleException.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     #[Validate('required', 'email')]
 *     public string $email;
 *
 *     #[Validate(['length', ['min' => 8]])]
 *     public string $password;
 *
 * References: ADR-003; SPEC-001 §7.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class Validate
{
    /** @var list<string|ValidationRule|array<array-key, mixed>> */
    public array $rules;

    /**
     * @param string|ValidationRule|array<array-key, mixed> ...$rules
     */
    public function __construct(string|ValidationRule|array ...$rules)
    {
        $named = [];
        $list = [];

        foreach ($rules as $key => $rule) {
            if (is_string($key)) {
                $named[] = $key;
            } else {
                $list[] = $rule;
            }
        }

        if ($named !== []) {
            throw InvalidRuleException::namedArguments(attribute: 'Validate', names: $named);
        }

        $this->rules = $list;
    }
}
