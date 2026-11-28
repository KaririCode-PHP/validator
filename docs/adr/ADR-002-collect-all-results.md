# ADR-002: Collect-All Results, Immutable, with Per-Field Limits and Nullable Fields

## Status

Accepted — V3.0.0

## Date

2026-11-28

## Context

A form or an API request usually has several problems at once. Stopping
at the first one makes people fix, resubmit and discover the next. The
tarball collected every error (its ADR-003) and offered `failFast` and
`maxErrorsPerField` per field. Its review found:

- `ValidationResult` was mutable (`addError()`, `merge()` changed it),
  so a result handed to an observer could change afterwards.
- The documented `nullable` ("always passes, skips the other rules when
  null") was implemented as "passes only for null" and the engine did
  not skip anything: `['nullable', ['length', ['max' => 100]]]` reported
  an error for every non-null value.
- `ValidationException` carried HTTP status 422 as its code, in a
  package that knows nothing about HTTP.

### Prior Art

- M. Fowler, "Notification" (2004): collect errors instead of throwing
  the first.
- RFC 9457 (Problem Details): APIs report every invalid field in one
  response.
- Laravel's `nullable` and `bail`: the semantics the tarball documented.

## Decision

1. **Collect-all by default.** The engine applies every rule of every
   field in order and records one `ValidationError` per failing rule.
2. **Per-field limits.** `failFast` stops a field at its first error;
   `maxErrorsPerField` (0: no limit) caps the errors of a field. Other
   fields still run.
3. **Immutable results.** `ValidationResult` is `final readonly`; the
   engine builds it once. `merge()` and `with()` return new results.
   It answers `isValid()`, `errorCount()`, `errorsFor()`,
   `firstErrorFor()`, `failedFields()` and `messages()` (field to
   messages, in the order the fields failed).
4. **Nullable.** A field whose rules include `nullable` and whose value
   is null (or absent) passes without running the other rules;
   `NullableRule` itself always passes.
5. **Exceptions only on request.** Input that breaks rules is a result,
   never an exception. The `ValidationProcessor` throws
   `ValidationException` (VAL_3230, Validation family) carrying the
   result, because a processing pipeline cannot return one.

## Consequences

### Positive

- One validation shows every problem; observers and callers share a
  result nobody can change.
- `nullable` does what its documentation always said.

### Negative

- **Breaking:** code that called `addError()` or `add()` on a result
  must build a new one; code that relied on the old `nullable` (passing
  only for null) can use `['equal_to', ['expected' => null]]`.
- Collect-all runs every rule even when the first already failed; use
  `failFast` where rules are expensive.

## Alternatives Considered

- **Fail-fast by default:** fewer rule calls, and one round trip per
  problem for whoever corrects the form.
- **A mutable result with a frozen view:** two types for one concept.
- **A `bail` rule instead of `failFast`:** per-field control in the rule
  map; can be added later without breaking the configuration flag.

## References

- M. Fowler, "Replacing Throwing Exceptions with Notification in
  Validations" (2014).
- RFC 9457, Problem Details for HTTP APIs.
- SPEC-001 §6 (INV-006, INV-007); ADR-004.
