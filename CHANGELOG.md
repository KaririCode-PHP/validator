# Changelog

All notable changes to `kariricode/validator` are documented here. The
format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and the project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] — 2026-11-28

A new line, built from the September 2026 component tarball (57 rules,
ARFA 1.3) with the alphanumeric CNPJ of the March 2026 main line, and
brought to KaririCode Specification V4.3.3. It replaces 2.0.0 (2024),
which was built on kariricode/processor-pipeline 1 and
kariricode/property-inspector 1. Read UPGRADE notes below before
updating from either.

### Spec V4.3.3 conformance (2026-11-28)

**Breaking**

- PHP 8.4. Requires `kariricode/contract` ^2.12 and `kariricode/exception`
  ^2.0.2; `kariricode/property-inspector` ^3.0 is a suggestion for
  attribute validation (ADR-003).
- `ValidationRule` has `validate()` and `violation(ValidationContext)`;
  `getErrorCode()` and `getDefaultMessage()` are gone (ADR-001, ADR-005).
- `ValidationContext`: `$field` (was `$fieldName`), `$data` (was
  `$rootData`), `$parameters`, `$group`; `parameter()` (was
  `getParameter()`, now distinguishing null from absent), typed readers
  `intParameter()`, `numberParameter()`, `stringParameter()`,
  `arrayParameter()`, and `hasField()`/`fieldValue()` by dotted path.
  `ValidationContextImpl` is `Core\RuleContext`.
- `MessageResolver::resolve($errorCode, $fallback, $parameters, $locale)`
  takes the rule's message as fallback; `DefaultMessageResolver` is
  immutable (`withMessages()` instead of `addMessage()`).
- `ValidationResult` is immutable (`merge()` and `with()` return new
  results; `add()` and `addError()` are gone); `ValidationError::$context`
  is `$parameters`.
- `ValidatorEngine::getConfiguration()` is the `$configuration` property;
  the constructor takes `registry`, `configuration`, `messages`,
  `observer`, all optional (`new ValidatorEngine()` uses the 52 built-in
  aliases).
- `AttributeValidator` takes a kariricode/contract `PropertyInspector`.
- `ProcessorBridge` is replaced by `Integration\ValidationProcessor`
  (contract `Processor` and `Validator`); `ValidatorServiceProvider`
  implements the contract `ServiceProvider` instead of offering
  factory methods.
- Exceptions carry VAL codes 3200–3299 on the kariricode/exception
  families (ADR-004): `InvalidRuleException` (Configuration),
  `ConfigurationException` (new), `ValidationException` (Validation, no
  longer code 422), `UnsupportedInputException` (new).
- Misconfiguration throws instead of passing or failing silently: a
  missing or mistyped parameter, an empty `choices`, `pattern`, `prefix`
  or `suffix`, an unknown `type`, a missing class for `instance_of`, an
  `ip` version other than 4 or 6, `length`, `count` and `range` without
  a bound, a malformed rule definition.
- `url` accepts http and https only, unless `schemes` says otherwise.
- `cpf`, `cep` and `pis` accept bare or formatted digits only; `cnpj`
  bare or formatted, upper case.
- `decimal` counts exponents ("1.5e-3" has 4 places).

**Added**

- Alphanumeric CNPJ (IN RFB nº 2.229/2024), all 26 letters (ADR-008).
- Messages complete in en, pt_BR and es; codes `string.length_min`,
  `string.length_max`, `numeric.range_min`, `numeric.range_max`,
  `collection.count_min`, `collection.count_max` name only the bound
  given; defaults the rule applied appear in the message (ADR-005).
- `ValidatorConfiguration::$customMessages` takes effect (it was
  documented and ignored), validated when built.
- `ValidationResult::messages()`, `with()`; `Violation`.
- Date rules accept `DateTimeInterface` values and bounds.
- `ValidationGroup` and the composite rules accept named variadic
  arguments.
- The quality gates through KaririCode Devkit, CI, SBOM, security
  checks (ADR-007); a conformance suite of 30 checks.

**Changed**

- Rules read their parameters before the value, so a misconfiguration
  shows whatever the input.
- `unique` compares by type and value (1, "1" and 1.0 are three items),
  objects by identity, without throwing for unserializable objects.
- Parameters merge with `withParameters()`; `withField()` keeps them.

**Fixed**

- `nullable` failed every non-null value, and the engine never skipped
  the other rules for null; it now does what it was documented to do.
- Patterns ended with `$`, so a trailing newline passed `alpha`,
  `alphanumeric`, `hostname`, `slug`, `uuid`, `ulid` and `bic`.
- `hostname` checked hyphens on the first label only
  ("example.-bad-.com" passed).
- `cpf`, `cep` and `pis` removed every non-digit first, so text around a
  number passed.
- `date_before` passed for the same day (unparsed fields took the
  current time); all date rules accepted dates PHP rolls over
  (2026-02-30).
- `url` accepted `javascript://…` and `file:///…`.
- `pattern`, `choice`, `starts_with`, `ends_with` passed every value when
  their parameter was missing; comparisons and `key_exists` failed every
  value.
- Numbers: booleans and INF counted as numbers in `positive`, large
  integers lost precision when compared as floats.
- `CallbackRule` and `ConditionalRule` raised a `TypeError` for closures
  that did not return a bool.
- Custom rules' messages were replaced by "Validation failed for …"
  whenever a resolver was set.

## [2.0.0] — 2024-10-25

The 2024 line (also 1.0.0 and 1.0.1, 2024-10-23), on PHP 8.3 with
kariricode/processor-pipeline 1 and kariricode/property-inspector 1.
See the git history.

## Upgrading from 2.x or the tarball

1. Replace `getErrorCode()`/`getDefaultMessage()` in custom rules with
   `violation()` returning `new Violation(code: …, message: …)`.
2. Read parameters with the typed readers; give `length`, `count` and
   `range` at least one bound, `pattern`, `choice` and the comparisons
   their parameters.
3. Build an `AttributeValidator` with a `PropertyInspector`
   (`composer require kariricode/property-inspector`).
4. Catch `ValidatorException` or a family instead of SPL exceptions.
5. Add `schemes` to `url` rules that must accept other schemes.
