<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

// ── Core: Blank ──────────────────────────────────────────────────────
// Decides whether a value counts as not filled in.
// ─────────────────────────────────────────────────────────────────────

/**
 * Says whether a value means "nothing was given": null, a string of
 * whitespace only, an empty array. Zero, "0" and false are values. The
 * required rules share this definition, so 'required' and
 * 'required_with' agree on what is missing.
 *
 * Preconditions: none.
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     Blank::is('   '); // true
 *     Blank::is(0);     // false
 *
 * References: SPEC-002 §1.
 */
final class Blank
{
    public static function is(mixed $value): bool
    {
        return $value === null || $value === [] || (is_string($value) && trim($value) === '');
    }
}
