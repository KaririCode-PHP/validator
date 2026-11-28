# Security Policy — kariricode/validator

Part of the KaririCode Framework. Security baseline: OWASP ASVS 5.0, target
level 2 (`extra.kariricode.asvs` in `composer.json`), KaririCode
Specification V4.3.3 §20–§24 and §32.

## Reporting a vulnerability

E-mail **walmir.silva@kariricode.org** with a description, the affected
version and a minimal reproduction. Do not open a public issue for
vulnerabilities. The fix timeline is agreed per report.

## Supported Versions

| Version | Status |
|---|---|
| 3.0.0 | Supported once tagged; QD-V433 PASS (`docs/quality/QD-V433-COMPLIANCE.md`) |
| 2.0.0 and earlier | Not supported; the 2024 line |

## Threat Model

The package checks data an application received before the application
trusts it. The rules, the rule maps, the attributes and the
configuration are trusted; the data is not: its values, types, sizes and
the presence of each field come from whoever sent it. The threats are a
value that passes a rule it should fail (and reaches a database, a
link, a document), a rule that silently stops checking because it was
written down wrongly, input that makes a rule do unbounded work, and a
message that leaks the input or internal detail.

```
client ─► data ─► ValidatorEngine ─► rules (pure functions of value, parameters, data)
                      │                 └── misconfiguration ─► InvalidRuleException (the application's)
                      └── ValidationResult: codes and messages (field names, never values)
```

| Threat | Control in this package | Residual risk | ASVS 5.0 |
|---|---|---|---|
| A value passing with a trailing newline or surrounding text | Every pattern anchors with `\A` and `\z`; `cpf`, `cep`, `pis`, `cnpj` match their format exactly instead of stripping characters | Rules that normalize on purpose (`iban`, `bic`, `luhn`) say so in SPEC-002 | V2.2.1 |
| Links to `javascript:`, `data:` or `file:` URLs | `url` allows http and https unless `schemes` lists others | Where the link points (SSRF, open redirects) is the application's check | V1.2.2, V2.2.1 |
| A rule that stops checking because of a typo | Missing or mistyped parameters, unknown aliases and types, malformed definitions throw with a VAL code; rules read parameters before values | A rule left out of the map is not detected | V2.2.1 |
| Unbounded work | Rules loop over characters or items once; `pattern` returns false when PCRE hits its backtracking limit instead of passing | The size of the input is bounded by the transport; a pattern the application writes can still be slow up to that limit | V1.3 (ReDoS) |
| Messages leaking input | Messages name fields and the rule's parameters, never the value; exception messages quote identifiers through kariricode/exception, which neutralizes control characters | Messages are plain text: HTML rendering must escape them | V16.5.1 |
| Values compared loosely | `choice`, `equal_to`, `same_as`, `unique` compare by type and value; numbers exclude booleans, INF and NAN | — | V2.2.1 |
| Shared state between requests | Rules, contexts, results and the engine are immutable; no static state | A registry is mutable until shared: register at boot | V15.4 |

## ASVS 5.0

Level 2 is the target. The controls above map to these tests:

| Control | Test |
|---|---|
| Anchored patterns, exact document formats | `StringRulesTest` (trailing newline cases), `DocumentRulesTest` |
| URL schemes | `StringRulesTest` (`url:` cases) |
| Misconfiguration throws | `misconfiguredRulesThrow` in every rule suite, `ValidatorEngineTest::malformedDefinitionsAreRefused`, `RuleContextTest` |
| Pathological input fails closed | `StringRulesTest` (`pattern: invalid UTF-8 under /u`) |
| Messages without values | `MessagesTest`, `ExceptionsTest` |
| Strict comparisons | `LogicRulesTest` (choice), `NumericRulesTest`, `CollectionAndCrossFieldRulesTest` |
| No static state | the conformance rule on static properties and variables |
| No secrets committed | `tools/secret-scan.php` and its self-test |

## Supply Chain

- Runtime dependencies are `kariricode/contract` and
  `kariricode/exception`; `kariricode/property-inspector` is suggested.
  No network, file or process access.
- `composer.lock` is committed, resolved from Packagist with a clean
  Composer home, with no path repository.
- `composer security` validates the manifest and the lock, audits the
  runtime lock and the toolchain lock (`.kcode/composer.lock`) for
  advisories, and scans the tree for secrets.
- `composer sbom` writes a CycloneDX 1.5 document; CI keeps it as a build
  artifact.
- The toolchain is pinned in `devkit.php`; CI installs kcode with a
  verified checksum, pins every action by its full commit SHA and runs
  the dependency review on pull requests.
- Release tags are created by the maintainer (Spec V4.3.3 §32).

## Hardening checklist for applications

- Validate on the server, before the data reaches storage, templates or
  other services; client-side checks are for convenience.
- Bound the size of request bodies before validating them.
- Register custom rules at boot, then share the engine.
- Escape messages where you render HTML.
- Treat `ValidatorException` from the Configuration family as a bug in
  the rule map, not as invalid input.
