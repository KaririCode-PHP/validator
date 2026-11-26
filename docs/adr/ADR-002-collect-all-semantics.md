# ADR-002: Collect-All Error Semantics

**Status:** Accepted
**Date:** 2026-03-06
**Component:** KaririCode\\Validator V3.1

## Context

Traditional validators stop at the first error. This forces users to submit forms multiple
times to discover all problems — poor UX. A "fail-fast" validator cannot provide
comprehensive error feedback in a single pass.

## Decision

The `ValidatorEngine` collects ALL errors for ALL fields before returning, implementing
**collect-all semantics**. `ValidationResult` carries a map of `field → list<ValidationError>`.
Each error includes the rule alias, the error key, and the i18n message.

## Alternatives Considered

- **Fail-fast**: Rejected — single per-run discovery cycle means 3+ form submissions.
- **Configurable mode**: Rejected — ARFA simplicity mandate; collect-all is always correct.

## Consequences

- Users receive a complete error map in one request.
- `ValidationResult::isValid()` returns `false` if any field has errors.
- Result is directly serialisable for API error responses.
