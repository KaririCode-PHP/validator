# ADR-004: Error Codes VAL 3200–3299 on the Exception Families; Misconfiguration Throws

## Status

Accepted — V3.0.0

## Date

2026-11-28

## Context

Spec V4.3.3 §8.4 identifies a failure by its kariricode/contract
exception family plus a numeric code that points to one component. The
tarball threw `\InvalidArgumentException` and `\RuntimeException`
without codes (its `ValidationException` used 422, an HTTP status), and
the main line did the same. The registry of record,
kariricode/exception's CODE-BLOCKS.md, had no block for the validator.

The package also has to draw a line the tarball blurred: which problems
are reported in the result and which are thrown.

### Prior Art

- kariricode/exception 2.0 (ADR-002 there): the registry, the family
  bases, `[PREFIX_NNNN]` messages with neutralized control characters.
- kariricode/graphql ADR-004, kariricode/logging ADR-004: the same move
  for their blocks.

## Decision

1. **The block.** VAL 3200–3299, the lowest free block of 100 codes
   (registry rule 3), laid out as 3200–3219 rule definitions and the
   registry, 3220–3229 settings and bindings, 3230–3239 the outcome of a
   validation, 3240–3249 integrations. `ValidatorCode` is the code table
   (backed, ARFA 1.3 E-003); the registry row is added with this
   release.
2. **Exceptions.** Every exception implements `ValidatorException`
   (`$errorCode`), extends a kariricode/exception base, belongs to one
   family and is built only by named constructors:

   | Codes | Exception | Family |
   |---|---|---|
   | 3201–3206 | InvalidRuleException | Configuration |
   | 3220–3221 | ConfigurationException | Configuration |
   | 3230 | ValidationException | Validation |
   | 3240 | UnsupportedInputException | Validation |

3. **The line.** Input that breaks rules is reported in the result.
   Mistakes in rules, rule maps, attributes, settings and container
   bindings are the application's and throw (Configuration family).
   Exceptions are thrown for input only where the caller cannot receive
   a result: the processor (VAL_3230, VAL_3240).
4. **Messages.** They name aliases, fields and parameters quoted with
   kariricode/exception's `quote()`, types instead of values, and never
   the input being validated.

## Consequences

### Positive

- A log line `[VAL_3204] A rule on field "age" needs the parameter
  "min"` names the component, the mistake and where to fix it.
- Catching `ValidatorException`, a family, or one class all work.

### Negative

- **Breaking:** code catching `\InvalidArgumentException` from the
  registry or reading `getCode() === 422` must change.
- The registry row must reach a kariricode/exception release before the
  registry test can read it from an installed package; until then the
  test accepts a free block (QD-V433 open items).

## Alternatives Considered

- **SPL exceptions with codes:** no family, no prefix, no registry.
- **Report misconfiguration in the result:** a typo in a rule map would
  look like invalid input and reach end users as a validation message.

## References

- Spec V4.3.3 §8.4; ARFA 1.3 E-003.
- kariricode/exception CODE-BLOCKS.md (rules 1–5), ADR-002.
- SPEC-001 §5 and §9.
