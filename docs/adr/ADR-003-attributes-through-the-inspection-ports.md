# ADR-003: Attribute Validation through the kariricode/contract Inspection Ports

## Status

Accepted — V3.0.0

## Date

2026-11-28

## Context

`#[Validate]` on a DTO's properties is the most convenient way to
declare rules, and it needs reflection: read the attributes, read the
values. The two lines of this package did it differently:

- The September 2026 tarball reflected by itself (`ReflectionClass`,
  `setAccessible()`), with no caching and no way to plug in another
  reader.
- The March 2026 main line used kariricode/property-inspector 2.0's own
  contracts, a hard dependency on a package that has since changed its
  API.

kariricode/property-inspector 3.0 (ADR-009 there) moved the inspection
contracts into kariricode/contract 2.12 (`PropertyInspector`,
`PropertyAttributeHandler`, `PropertyChangeApplier`) so that "the
processors' handlers implement `PropertyAttributeHandler`", and rejected
"each consumer keeps its own reflection" as an alternative.

### Prior Art

- kariricode/property-inspector ADR-008 (object-typed inspection API)
  and ADR-009 (ecosystem contract alignment, 3.0).
- kariricode/contract ADR-007 (Inspection namespace).
- Symfony Validator's metadata factories: a reader behind an interface,
  cached by the implementation.

## Decision

1. `AttributeValidator` takes a `ValidatorEngine` and a
   `KaririCode\Contract\Inspection\PropertyInspector`. It inspects the
   object for `#[Validate]` and, when a group is given, for
   `#[ValidationGroup]`, and validates the collected values.
2. `Core\AttributeRules` implements `PropertyAttributeHandler`: it
   collects the rules and values of `#[Validate]` properties and the
   groups of `#[ValidationGroup]`; a property without a group belongs to
   every group.
3. kariricode/property-inspector is a `suggest`, not a requirement:
   array validation needs no reflection, and any implementation of the
   contract works. The README and the provider point to
   `ReflectionPropertyInspector`.
4. The only `object` parameters of the package are the contract's:
   `AttributeRules::handleAttribute($attribute)` and
   `AttributeValidator::validate($object)`, pinned by the conformance
   suite as accepted deviations, as property-inspector's ADR-008 does.
5. `#[Validate]` takes positional definitions only; named arguments
   throw `InvalidRuleException` (VAL_3206), because PHP would collect
   them under string keys the engine cannot read.

## Consequences

### Positive

- One reflection implementation, with its cache, for the whole
  processing family; the validator depends on interfaces only.
- The test suite runs the handler against a contract implementation of
  its own, and the README example was checked against
  kariricode/property-inspector 3.0.0 from Packagist.

### Negative

- Attribute validation needs a second package (or an own
  implementation of three short interfaces).
- What an uninitialized property means is the inspector's decision:
  `ReflectionPropertyInspector` throws, the tarball read null.
- Cross-field rules see only the properties that carry `#[Validate]`.

## Alternatives Considered

- **Keep the tarball's reflection:** works without a second package and
  duplicates property-inspector, which ADR-009 there rejected.
- **Require kariricode/property-inspector:** simpler installation, and a
  hard dependency array validation does not need.
- **Read all properties for cross-field rules:** needs a second
  inspection pass without an attribute class, which the contract does
  not offer.

## References

- kariricode/property-inspector ADR-008, ADR-009; kariricode/contract
  ADR-007.
- Spec V4.3.3 §30 (object parameters), §31 (dependencies).
- SPEC-001 §7.
