# SPEC-001: KaririCode\Validator 3.0 — The Engine

- **Version:** 3.0.0
- **Standards:** KaririCode Specification V4.3.3; ARFA 1.3 level 1 (Structural); OWASP ASVS 5.0 level 2
- **Error code block:** VAL 3200–3299 (ADR-004)

## 1. Purpose

Validate data against rules the application declares: arrays by a rule
map, objects by `#[Validate]` attributes. Report every failure with a
message a person can read; refuse rules that are written down wrongly.
The rules themselves are specified in SPEC-002.

## 2. Architecture

```
src/
├── Attribute/      Validate, ValidationGroup
├── Configuration/  ValidatorConfiguration
├── Contract/       ValidationRule, ValidationContext, RuleRegistry, MessageResolver
├── Core/           ValidatorEngine, RuleContext, InMemoryRuleRegistry, DefaultMessageResolver, MessageCatalog,
│                   AttributeValidator, AttributeRules; DataPath, NumericValue, DateValue, Blank (shared by rules)
├── Event/          ValidationStartedEvent, ValidationCompletedEvent
├── Exception/      ValidatorCode, the marker ValidatorException and four final exceptions
├── Integration/    ValidationProcessor (kariricode/contract Processor and Validator)
├── Provider/       ValidatorServiceProvider (kariricode/contract ServiceProvider)
├── Result/         ValidationResult, ValidationError, Violation
└── Rule/           57 rules in Brazilian, Collection, Comparison, CrossField, Date, Financial, Logic, Numeric, String, Type
```

```
validate(data, fieldRules, group)
  ├── ValidationStartedEvent ─► observer
  ├── for each field: RuleContext(data, field, group)
  │     ├── definitions → [rule, parameters] (InvalidRuleException when malformed)
  │     ├── value = DataPath(data, field); null and 'nullable' → skip
  │     └── for each rule: validate(value, context + parameters)
  │           └── false → violation() → MessageResolver → ValidationError
  │                 └── failFast / maxErrorsPerField → stop the field
  ├── ValidationResult (immutable)
  └── ValidationCompletedEvent ─► observer
```

## 3. Rules

`ValidationRule::validate(mixed $value, ValidationContext $context): bool`
decides; `violation(ValidationContext $context): Violation` describes a
failure. Invariants:

| ID | Invariant |
|---|---|
| R-1 | A rule holds no state between calls; built-in rules are `final readonly`. |
| R-2 | A rule returns false for value types it does not handle; it never throws for input. |
| R-3 | A rule reads its parameters before the value, through the typed readers, so a misconfiguration throws whatever the value. |
| R-4 | `violation()` is called with the context `validate()` returned false for. |

## 4. The Context

`RuleContext` implements `ValidationContext`: `field`, `data` (the whole
input), `parameters`, `group`; `withField()` and `withParameters()`
return copies (parameters merge, later wins).

| Reader | Accepts | Missing without default | Wrong type |
|---|---|---|---|
| `parameter($name, $default)` | anything | `$default` | — |
| `intParameter()` | int | VAL_3204 | VAL_3205 |
| `numberParameter()` | int, finite float | VAL_3204 | VAL_3205 |
| `stringParameter()` | string | VAL_3204 | VAL_3205 |
| `arrayParameter()` | array | VAL_3204 | VAL_3205 |

`hasField($path)` and `fieldValue($path)` read any field by its path
(INV-005): a literal key first, else segments split at dots, integer
keys by their digits; a missing field reads as null.

## 5. Rule Maps and Definitions

`validate(array $data, array $fieldRules, ?string $group)`. Each key of
`$fieldRules` is a field path (integer keys become strings); each value
is a list of definitions:

| Definition | Meaning |
|---|---|
| `'alias'` | the registry's rule, no parameters |
| `ValidationRule` | that rule, no parameters |
| `[alias or rule]` | the same |
| `[alias or rule, parameters]` | with parameters, a map of names to values |

Anything else throws `InvalidRuleException::invalidDefinition`
(VAL_3203); an unknown alias throws VAL_3202.

## 6. Results, Errors and Violations

| Type | Content |
|---|---|
| `Violation` | `code`, `message` (English, with placeholders), `parameters` (computed by the rule) |
| `ValidationError` | `field`, `errorCode`, `message` (resolved), `parameters` (rule parameters and violation parameters) |
| `ValidationResult` | `errors` in rule order; `isValid()`, `errorCount()`, `errorsFor()`, `firstErrorFor()`, `failedFields()`, `messages()`, `merge()`, `with()` |

Invariants:

| ID | Invariant |
|---|---|
| INV-001 | Every rule of every field runs unless a limit stops the field. |
| INV-002 | A failing rule adds exactly one error. |
| INV-003 | Errors keep the order of fields and rules. |
| INV-004 | A missing field validates as null. |
| INV-005 | Field paths are dotted; a literal key wins. |
| INV-006 | `failFast` stops a field at its first error. |
| INV-007 | `maxErrorsPerField > 0` caps the errors of a field. |
| INV-008 | A field whose rules include `nullable` and whose value is null runs no rule. |
| INV-009 | Results are immutable. |

Messages: the resolver receives the violation's code and message and
the parameters plus `field`. `DefaultMessageResolver` uses the locale
asked for, else the configured one, then `en`, then the fallback;
placeholders are filled by type (ADR-005).

## 7. Attributes

`#[Validate(...$definitions)]` (repeatable, positional only, VAL_3206 for
named arguments) and `#[ValidationGroup(...$groups)]`.
`AttributeValidator::validate(object $object, ?string $group)` inspects
`Validate` (and, with a group, `ValidationGroup`) through a
kariricode/contract `PropertyInspector` into `AttributeRules`, then
validates the values of the properties in the group (a property without
group is in every group). Cross-field rules see the properties with
`#[Validate]`.

## 8. Configuration

| Setting | Default | Constraint |
|---|---|---|
| `defaultLocale` | `en` | non-empty |
| `failFast` | false | — |
| `maxErrorsPerField` | 0 (no limit) | ≥ 0 |
| `customMessages` | `[]` | locale ⇒ (error code ⇒ message), strings |

Violations throw `ConfigurationException::invalidSetting` (VAL_3220).

## 9. Exceptions

| Code | Class | Family | Named constructors |
|---|---|---|---|
| 3201 | InvalidRuleException | Configuration | duplicateAlias |
| 3202 | InvalidRuleException | Configuration | unknownAlias |
| 3203 | InvalidRuleException | Configuration | invalidDefinition, emptyAlias, noRules |
| 3204 | InvalidRuleException | Configuration | missingParameter, missingBounds |
| 3205 | InvalidRuleException | Configuration | invalidParameter |
| 3206 | InvalidRuleException | Configuration | namedArguments |
| 3220 | ConfigurationException | Configuration | invalidSetting |
| 3221 | ConfigurationException | Configuration | invalidBinding |
| 3230 | ValidationException | Validation | failed (carries the result) |
| 3240 | UnsupportedInputException | Validation | notAnArray |

## 10. Integrations

- `ValidationProcessor(engine, fieldRules, group)`: `process()` returns
  valid input unchanged, else throws VAL_3230; `validate()` returns the
  result; non-arrays throw VAL_3240.
- `ValidatorServiceProvider(configuration, observer)`: binds
  `RuleRegistry`, `MessageResolver`, `ValidatorEngine` and
  `AttributeValidator` as singletons; the last resolves the container's
  `PropertyInspector`. A binding of the wrong type throws VAL_3221.
- Observer: `ValidationStartedEvent(fields, group, timestamp)`,
  `ValidationCompletedEvent(result, durationMs)`.

## 11. ARFA 1.3 and Spec V4.3.3

| Rule | How |
|---|---|
| P1 immutable state | rules, contexts, results, events, settings, the engine and the default resolver are `final readonly`; the registry is mutable until shared |
| P2 flow | definitions → rules → violations → messages → result |
| P4 protocol agnosticism | arrays and objects in, a result out; no HTTP, no I/O |
| P5 observability | coded exceptions; start and completion events |
| E-003 pure enums | ValidatorCode is the only enum, backed for `getCode()` |
| §13, §17, §18, §29, §30, §32 | checked by the conformance suite and the gates (ADR-007) |

## 12. File Inventory

| Directory | Files |
|---|---|
| src/Attribute | 2 |
| src/Configuration | 1 |
| src/Contract | 4 |
| src/Core | 11 |
| src/Event | 2 |
| src/Exception | 6 |
| src/Integration | 1 |
| src/Provider | 1 |
| src/Result | 3 |
| src/Rule | 57 |
| tests/Unit | 13 |
| tests/Integration | 1 |
| tests/Conformance | 2 |
| tests/Support | 4 |
