# ADR-004: Internationalised Error Messages

**Status:** Accepted
**Date:** 2026-03-06
**Component:** KaririCode\\Validator V3.1

## Context

Validation errors must be user-facing. Hard-coded English strings are not acceptable for
a multi-tenant, multi-locale framework, but external i18n libraries violate the zero-dep mandate.

## Decision

Each rule defines its error conditions as **message keys** (not strings):
`required`, `email_format`, `min_length`, etc. The `MessageBag` resolves keys to locale-specific
strings from a PHP array-based message catalogue bundled in `src/Message/`. Default locale is
`en`. Callers can swap the catalogue or inject their own `MessageResolver` to support
additional locales.

## Consequences

- Zero third-party i18n dependency.
- Error keys are stable across locales — API clients use them, humans see translated text.
- Message files are plain PHP arrays — trivial to override per project.
