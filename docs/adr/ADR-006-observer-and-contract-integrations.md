# ADR-006: An Observer for Events; the Contract Processor, Validator and ServiceProvider

## Status

Accepted — V3.0.0

## Date

2026-11-28

## Context

ARFA 1.3 P5 asks components to be observable, and the ecosystem wires
components through kariricode/contract ports. The tarball emitted
`ValidationStartedEvent` and `ValidationCompletedEvent` to an optional
closure; its `ProcessorBridge` returned `['data' => …, 'result' => …]`
or threw, depending on a flag, and implemented no contract; its
`ValidatorServiceProvider` was a factory that implemented nothing either.

### Prior Art

- kariricode/template and kariricode/i18n: providers implementing
  `KaririCode\Contract\DI\ServiceProvider`.
- kariricode/logging ADR on the processor-pipeline bridge: a contract
  `Processor` that returns the processed value or throws.
- PSR-14: an event dispatcher is a callable away from a closure.

## Decision

1. **Observer.** `ValidatorEngine` takes an optional `Closure` and calls
   it with a `ValidationStartedEvent` (field names, group, Unix time)
   and a `ValidationCompletedEvent` (result, duration in milliseconds
   from the monotonic clock) per validation. Events carry names and
   messages, never values. A PSR-14 dispatcher plugs in as
   `static fn (object $event) => $dispatcher->dispatch($event)`.
2. **Processor and Validator.** `Integration\ValidationProcessor`
   implements kariricode/contract's `Processor` and `Validator` for a
   fixed rule map: `process()` returns valid input unchanged and throws
   `ValidationException` with the result otherwise; `validate()` returns
   the `ValidationResult`. Input that is not an array throws
   `UnsupportedInputException`.
3. **ServiceProvider.** `ValidatorServiceProvider` implements
   kariricode/contract's `ServiceProvider` and binds, as singletons, the
   registry with the built-in rules, the message resolver for the
   configured locale, the engine and the `AttributeValidator`, which
   resolves the container's `PropertyInspector`. A binding of the wrong
   type throws `ConfigurationException` (VAL_3221).

## Consequences

### Positive

- The validator joins a kariricode/processor-pipeline as one step and a
  framework container as one provider, with nothing but kariricode/contract.
- Metrics and logs need no dependency in this package.

### Negative

- **Breaking:** `ProcessorBridge` and its `throwOnFailure` flag are gone;
  call the engine directly to get a result without an exception.
- The observer runs synchronously in the validation; a slow observer
  slows every validation.

## Alternatives Considered

- **Require psr/event-dispatcher:** one more dependency for what a
  closure already does.
- **Keep the factory provider:** convenient without a container, and
  `new ValidatorEngine()` already is that convenience.

## References

- ARFA 1.3 P5; kariricode/contract Processor, Validator, DI\ServiceProvider.
- PSR-14, Event Dispatcher.
- SPEC-001 §10.
