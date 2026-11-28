# ADR-001: Stateless Rules, a Registry of Aliases and Rule Definitions

## Status

Accepted — V3.0.0

## Date

2026-11-28

## Context

A validator has to let applications say, per field, which checks apply
and with which settings, and has to let them add checks of their own.
The September 2026 tarball (the base of 3.0) had the right shape: a
`ValidationRule` interface, an in-memory registry of aliases and an
engine that accepted an alias, a rule or `[alias, parameters]` per
definition. It also had problems the review found:

- Parameters were read with `getParameter()` and cast: `(int) 'abc'`
  became 0, a missing `min` became 0 or "no bound", a missing `pattern`
  made `pattern` pass everything, a missing `choices` made `choice` pass
  everything, a missing `threshold` made comparisons fail everything.
- A malformed definition (`[42]`, `['length', 'oops']`) reached the
  rule as a PHP `TypeError` or was skipped.
- The rule interface exposed `getErrorCode()` and `getDefaultMessage()`,
  parameterless getters Spec V4.3.3 §18 rejects, and had no way to name
  a bound applied by default in its message (ADR-005).

### Prior Art

- The Specification pattern (Evans and Fowler, 2002): small predicates
  composed into larger ones.
- Laravel's string rules (`'min:3'`) and Symfony's constraint objects:
  two ends of the same idea; the tarball's array form sits between them
  and keeps parameters typed.
- kariricode/graphql ADR-001 and kariricode/logging: values checked
  where they enter, coded exceptions for the application's mistakes.

## Decision

1. **Rules are stateless.** A rule implements
   `validate(mixed $value, ValidationContext $context): bool` and
   `violation(ValidationContext $context): Violation`; everything that
   varies per use comes from the context. Every rule class is
   `final readonly`, so one instance serves every field and request.
   Rules return false for types they do not handle; they never throw for
   input.
2. **Definitions.** A field's rules are a list. Each definition is an
   alias, a `ValidationRule`, or a list `[alias or rule, parameters]`
   whose parameters are a map of names to values. Anything else throws
   `InvalidRuleException` (VAL_3203).
3. **Typed parameters.** The context reads parameters with
   `intParameter()`, `numberParameter()` (finite int or float),
   `stringParameter()` and `arrayParameter()`, with an optional
   default. A missing parameter without default throws VAL_3204, a
   parameter of the wrong type VAL_3205. Rules read their parameters
   before they look at the value, so a misconfiguration surfaces on the
   first validation whatever the input.
4. **The registry.** `InMemoryRuleRegistry::withDefaults()` registers
   the 52 rules that take their settings from parameters; the five rules
   that wrap other rules or closures (`EachRule`, `CallbackRule`,
   `CompositeRule`, `ConditionalRule`, `AtLeastOneOfRule`) are built in
   code and registered under an alias of the application's choice. An
   empty alias, a duplicate alias and an unknown alias throw
   (VAL_3203, VAL_3201, VAL_3202).
5. **Field paths.** A field is a dotted path into nested arrays; a key
   that contains dots itself wins. Cross-field rules read other fields
   by the same paths through `hasField()` and `fieldValue()`.

## Consequences

### Positive

- A typo in a rule map fails loudly in the first test that runs it,
  instead of silently passing or failing every value.
- Custom rules have one small interface; rule instances can be shared
  freely, also across fibers and workers.

### Negative

- **Breaking:** custom rules written for 2.x or for the tarball must
  implement `violation()` instead of `getErrorCode()` and
  `getDefaultMessage()`, and read parameters through the typed readers.
- Parameters that a configuration file delivers as strings ("3") must
  be converted before they reach the rule map.

## Alternatives Considered

- **Rules configured in their constructors** (`new LengthRule(min: 3)`):
  typed by PHP itself, but every alias would need a factory, and
  attributes would carry objects for every rule. Kept for the composite
  rules only.
- **String syntax** (`'length:3,10'`): compact, but parameters lose
  their types and need a parser with its own escaping rules.
- **Casting parameters as before:** keeps old configurations running and
  keeps hiding their mistakes.

## References

- Spec V4.3.3 §8.4 (exception families), §18 (accessors), §30.
- E. Evans, M. Fowler, "Specifications" (2002).
- SPEC-001 §3–§5; ADR-004; ADR-005.
