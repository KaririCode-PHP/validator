# ADR-003: Validation Context Immutability

**Status:** Accepted
**Date:** 2026-03-06
**Component:** KaririCode\\Validator V3.1

## Context

Rules receive a `ValidationContext` to access the full data array (for cross-field rules).
Mutable context would allow rules to corrupt shared state.

## Decision

`ValidationContextImpl` is `final readonly class`. Builder pattern — `with*()` returns new
instances. The engine creates one context per field per rule invocation.

## Consequences

- Zero cross-field state pollution.
- Future parallel validation is safe.
