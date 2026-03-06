# ADR-005: Cross-Field Validation via ValidationContext

**Status:** Accepted
**Date:** 2026-03-06
**Component:** KaririCode\\Validator V3.1

## Context

Rules like `same_as` (password confirmation) and `unique` (DB check) require access to
fields beyond the one currently being validated.

## Decision

`ValidationContext::getData()` exposes the full input array. Rules that need cross-field
access call `$context->getData()['confirm_password']` internally. This keeps the rule
contract simple (receives one $value + context) while still enabling complex scenarios.

Rules that require external I/O (e.g. DB `unique`) MUST declare side effects in their
PHPDoc (C3 criterion, ARFA Spec Section 9.2).

## Consequences

- Cross-field rules are standard rules — no special engine hook required.
- Side-effect rules are explicitly flagged in documentation.
