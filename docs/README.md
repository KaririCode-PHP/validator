# KaririCode\Validator — Documentation

## Architecture

```
array data + rule map ────────────────► ValidatorEngine::validate(data, fieldRules, group)
                                           │  ValidationStartedEvent ─► observer
object + #[Validate] ─► AttributeValidator │
   PropertyInspector (kariricode/contract) │  per field: RuleContext, DataPath, 'nullable'
   AttributeRules (the handler) ───────────┤  per rule:  RuleRegistry ─► ValidationRule
                                           │             validate() false ─► violation()
                                           │             MessageResolver (catalog en, pt_BR, es)
                                           │  ValidationResult (immutable) ─► caller
                                           └  ValidationCompletedEvent ─► observer

kariricode/processor-pipeline ─► ValidationProcessor: input unchanged, or ValidationException
kariricode/contract container ─► ValidatorServiceProvider: registry, resolver, engine, AttributeValidator
```

## Index

| Document | Subject |
|---|---|
| [ADR-001](adr/ADR-001-rules-registry-and-definitions.md) | Stateless rules, a registry of aliases, typed parameters |
| [ADR-002](adr/ADR-002-collect-all-results.md) | Collect-all results, per-field limits, nullable fields |
| [ADR-003](adr/ADR-003-attributes-through-the-inspection-ports.md) | Attribute validation through the kariricode/contract Inspection ports |
| [ADR-004](adr/ADR-004-error-codes-and-misconfiguration.md) | Error codes VAL 3200–3299; misconfiguration throws |
| [ADR-005](adr/ADR-005-violations-messages-and-locales.md) | Violations, the message catalog, fallbacks |
| [ADR-006](adr/ADR-006-observer-and-contract-integrations.md) | The observer, the contract Processor, Validator and ServiceProvider |
| [ADR-007](adr/ADR-007-toolchain-through-kcode.md) | Quality gates through KaririCode Devkit |
| [ADR-008](adr/ADR-008-alphanumeric-cnpj.md) | The alphanumeric CNPJ |
| [SPEC-001](spec/SPEC-001-validation-engine.md) | The engine: context, definitions, results, attributes, configuration |
| [SPEC-002](spec/SPEC-002-rule-reference.md) | The 57 rules |
| [QD-V433-COMPLIANCE](quality/QD-V433-COMPLIANCE.md) | Quality record against Spec V4.3.3 |
