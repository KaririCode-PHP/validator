# ADR-005: Violations, a Message Catalog in Three Locales, and Fallbacks

## Status

Accepted — V3.0.0

## Date

2026-11-28

## Context

Messages are what people read. The tarball resolved them from a catalog
(en complete, pt_BR complete, es with 7 entries) by the rule's error
code, interpolating `{placeholders}` from the rule's parameters. The
review found:

- A bound applied by default never reached the message: `decimal`
  without `places` produced "at most {places} decimal places", and
  `length` with only `min` produced "between 8 and {max} characters".
- A custom rule's own message was ignored whenever a resolver was set,
  because the resolver answered "Validation failed for custom.even."
  for codes it did not know.
- `ValidatorConfiguration::$customMessages` was documented and never
  used.
- The resolver was mutable (`addMessage()`), shared by every request.

### Prior Art

- Symfony Validator: a violation carries a template and its parameters;
  the translator resolves the template.
- ICU MessageFormat placeholders (`{name}`), without plural selection.

## Decision

1. **Violations.** `ValidationRule::violation($context)` returns a
   `Violation`: the error code, the English message the rule falls back
   to, and placeholder values the rule computed (a default it applied).
   Rules whose message depends on their parameters report a code per
   case: `string.length`, `string.length_min`, `string.length_max`, and
   likewise for `numeric.range` and `collection.count`.
2. **Placeholders.** The engine fills a message from the rule's
   parameters, the violation's parameters and `field`. Strings and
   numbers are written as they are, booleans as true and false, lists of
   scalars joined with commas, dates as Y-m-d (with the time when it is
   not midnight); other values leave the placeholder untouched.
3. **Fallback chain.** `DefaultMessageResolver` looks up the code in the
   requested locale, then in English, then uses the rule's fallback.
4. **Catalog.** `Core\MessageCatalog` holds one message per code of the
   built-in rules in en, pt_BR and es, all three complete. A test checks
   that every code a built-in rule reports exists in the three locales,
   that the English message equals the rule's fallback, and that
   translations use the same placeholders.
5. **Configuration.** `defaultLocale` picks the locale;
   `customMessages` (locale to code to message) replace or add messages
   and are validated when the configuration is built.
   `DefaultMessageResolver` is immutable; `withMessages()` returns a
   copy. Any `MessageResolver` can replace it, for example a translator.

## Consequences

### Positive

- Every built-in message reads complete, in three languages, whatever
  parameters the rule map omits.
- Custom rules keep their messages; translations can be added per code.

### Negative

- **Breaking:** the resolver interface takes the fallback
  (`resolve($errorCode, $fallback, $parameters, $locale)`); a resolver
  written for 2.x must add it. Clients that keyed on `string.length`
  for min-only rules must accept the new codes.
- Messages are plain text; HTML rendering must escape them.

## Alternatives Considered

- **Rules declare default parameters** and the engine merges them:
  covers `decimal`, not the min-only and max-only wording.
- **One message per rule with optional sections:** needs a template
  language; three codes are simpler for translators.
- **Leave unresolved placeholders out of the text:** produces "between
  8 and characters".

## References

- Unicode ICU MessageFormat (placeholder syntax).
- Symfony Validator, ConstraintViolation.
- SPEC-001 §6; SPEC-002.
