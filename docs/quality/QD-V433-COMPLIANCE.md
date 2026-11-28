# QD-V433-COMPLIANCE: kariricode/validator 3.0.0

- **Status: PASS** (automated gates)
- **Date:** 2026-11-28
- **Component:** kariricode/validator 3.0.0; kariricode/contract ^2.12 (2.12.1 locked), kariricode/exception ^2.0.2 (2.0.2 locked); kariricode/property-inspector ^3.0 suggested
- **Standards:** KaririCode Spec V4.3.3 §29 (gates) and §32 (supply chain); ARFA 1.3 level 1 (Structural); OWASP ASVS 5.0 level 2
- **Authors:** Walmir Silva <walmir.silva@kariricode.org>

Every automated gate of §29.1 passes, locally (§1) and in CI: run
[46153569228](https://github.com/KaririCode-PHP/validator/actions/runs/46153569228)
passed the gates on PHP 8.4.28 and the suites on PHP 8.5.13 with the
lowest dependencies (kariricode/contract 2.12.0, kariricode/exception
2.0.2), at `f95d2bf`. The status turns FAIL if a gate fails. PASS covers
the automated gates (G1, G4, G5, G6); G3 needs a second reviewer (§8). No
score is assigned (E-006). This is the first quality record of the 3.x
line: the September 2026 tarball reported 729 PHPStan errors at level
max and 217 Psalm errors at level 1, with no mutation testing.

## 1. Gates (Spec V4.3.3 §29.1)

Measured locally on 2026-11-28 (§2). CI reported the same tests (565,
6,692 assertions) and the same mutants (1,077: 1,074 killed, 3 escaped).

| Gate | Command | Budget | Measured | Result |
|---|---|---|---|---|
| Tests | `composer test` | all pass, no warning, notice, deprecation or risky test | 565 tests: unit 532, integration 3 (a company form in Portuguese, a pipeline, a DTO per group), conformance 30 | pass |
| Conformance | `composer test-conformance` | all pass | 30 tests: `ArfaConformanceTest` 27 (25 rules and 2 documentation checks, §3), `CodeBlockRegistryTest` 3 | pass |
| PHPStan | `composer phpstan` | level max on `src` and `tests`, strict and PHPUnit rules, cognitive complexity ≤ 15 | 0 errors | pass |
| Psalm | `composer psalm` | level 1 on `src`, no baseline | 0 errors; `#[\Override]` required; `NoInterfaceProperties` suppressed in `psalm.xml` (tool limitation, ADR-007) | pass |
| Coding standard | `composer cs-check` | PER-CS (E-045, E-046) | 0 of 108 files to fix | pass |
| Complexity | `composer complexity`, `composer phpstan` | cyclomatic ≤ 10, nesting ≤ 3, parameters ≤ 4 per method of `src`; cognitive ≤ 15 (§13) | 238 methods; no accepted breach (empty baseline); one soft warning: `RuleContext` has 11 public methods (soft budget 10) | pass |
| Mutation | `composer mutation` | MSI ≥ 80 and covered-code MSI ≥ 80 (§19.4) | 1,077 mutants: 1,074 killed, 3 escaped; MSI 99.72 %, covered-code MSI 99.72 %; mutation code coverage 100 % (§7) | pass |
| Security | `composer security` | valid manifest and lock, no advisory, no secret | manifest and lock valid; no advisories in the runtime lock or in `.kcode/composer.lock`; secret scan 0 findings (self-test ok) | pass |

Line coverage of `src` by the unit and integration suites is 100 % (596
of 596 statements; PCOV, `phpunit --coverage-clover`); §29.1 sets no
coverage budget, mutation testing is the gate (§7).

`composer sbom` writes a CycloneDX 1.5 document with 2 runtime components
(kariricode/contract 2.12.1, kariricode/exception 2.0.2).

The README examples were run against kariricode/property-inspector
3.0.0 from Packagist (attribute validation through its
`ReflectionPropertyInspector`), outside the repository.

## 2. Environment and Metadata

| Item | Value |
|---|---|
| Runtime | PHP 8.4.28 CLI, PCOV |
| Toolchain | KaririCode Devkit 1.2.2 with the root configs (ADR-007): PHPUnit 11.5.56, PHPStan 2.3.7 with phpstan-strict-rules 2.1.2 and phpstan-phpunit 2.1.4, Psalm 6.19.1, PHP-CS-Fixer 3.95.27, Infection 0.35.5, tomasvotruba/cognitive-complexity 1.2.0 |
| Lock | `composer.lock` resolved from Packagist with a clean Composer home; no path repository |
| `extra.kariricode` (gate G4, errata E-044) | `arfa` 1.3 level 1, `asvs` 5.0 level 2, `spec` 4.3.3, `error-code-range` VAL 3200–3299 |
| Error codes | `ValidatorCode`, 10 codes in 3201–3240, prefix VAL; the block is claimed on kariricode/exception's main, not yet in a release (§8) |

## 3. Conformance Rules (§29.2)

`tests/Conformance/ArfaConformanceTest.php`; all 27 pass.

| Rule | Reference |
|---|---|
| Every source file is strict and declares exactly one named type | §25, PSR-4 |
| Docblocks meet the five comment criteria (C1 summary; Preconditions, Thread-safety, Side effects, Example, References) | §17 |
| Section markers are 72 columns wide (sources and tests) | §25 |
| Concrete classes are final | §5 |
| Values (9 listed: attributes, settings, the rule context, events, results and violations) are final readonly | §5.2 |
| Readonly classes declare no property hooks, nor `readonly` again inside | E-040 |
| Class constants are typed | §25 |
| Inheritance inside the package is at most two levels deep | §18 |
| No type keeps mutable static state | §5 |
| Names and methods avoid the forbidden patterns (Interface suffix, Manager/Helper/Util, toArray/fromArray/jsonSerialize, magic accessors) | §18, E-001 |
| Public methods are not bare getters (no parameter) or setters (one parameter) | §18 |
| Regular expressions anchor the end with `\z`, never `$` | §24 |
| Call sites with three or more arguments name them (the printf family and calls of a callable the application supplies are exempt) | §30 |
| Contracts are interfaces (4) | §5, §31 |
| Enums are pure unless an external contract reads the value (`ValidatorCode`), with the justification written down | E-003 |
| Every directory is a namespace segment | PSR-4 |
| Error codes are unique and inside the reserved block | §8 |
| Exceptions are built only by their named constructors; nothing throws an uncoded exception | §8 |
| Every concrete exception belongs to exactly one contract family, implements the package marker and extends a kariricode/exception base | §8.4 |
| `composer.json` declares the ARFA, ASVS and Spec versions and the code range; the runtime is PHP, kariricode/contract and kariricode/exception; property-inspector is suggested; no `require-dev` | §29.3 G4, E-044, §31 |
| kariricode/contract and kariricode/exception namespaces are imported only where they belong (Inspection by the attribute classes and the provider, DI by the provider, Processor and Validator by the integration, the exception bases by `Exception\`) | §31 |
| The 57 rules are final readonly and implement `ValidationRule` | §5.2, ADR-001 |
| `object` parameters only where the kariricode/contract Inspection ports have them | §30, ADR-003 |
| `composer.json` exposes the eight quality gates | §29.1 |
| Supply chain is pinned: lock committed, CI actions by commit SHA | §32 |

Two further tests check the documentation: 8 ADRs with the seven
canonical sections, this record's status line, the SECURITY.md sections
and the CHANGELOG headings; and that the README, SECURITY.md,
CONTRIBUTING.md, the ADRs, the SPECs, this record and the sources cite
Spec V4.3.3.

`CodeBlockRegistryTest` checks that `ValidatorCode` is a valid code
table, that the installed CODE-BLOCKS.md leaves 3200–3299 and the prefix
VAL to this package (and, where it lists the block, that the row
matches), and the documented sub-ranges.

## 4. Error Model (§8)

Every exception implements the marker `ValidatorException`, which
declares `$errorCode` (ADR-004): `InvalidRuleException` (Configuration;
3201–3206), `ConfigurationException` (Configuration; 3220–3221),
`ValidationException` (Validation; 3230, carrying the result) and
`UnsupportedInputException` (Validation; 3240). `getCode()` is the
`ValidatorCode` number; messages start with `[VAL_<code>]` and quote
identifiers. `validate()` throws only for mistakes in rules, rule maps
and settings; invalid input is a result.

## 5. Security Controls (ASVS 5.0, level 2 declared)

| Control | Test |
|---|---|
| Patterns anchored, document formats exact (V2.2.1) | `StringRulesTest`, `DocumentRulesTest` |
| URL schemes allow-listed (V1.2.2) | `StringRulesTest` (`url:` cases) |
| Misconfiguration throws instead of disabling a check (V2.2.1) | the `misconfiguredRulesThrow` tests, `ValidatorEngineTest::malformedDefinitionsAreRefused`, `RuleContextTest` |
| Input PCRE cannot match fails closed (V1.3) | `StringRulesTest` (`pattern: invalid UTF-8 under /u`) |
| Messages without values (V16.5.1) | `MessagesTest`, `ExceptionsTest` |
| Strict comparisons | `LogicRulesTest`, `NumericRulesTest`, `CollectionAndCrossFieldRulesTest` |
| No static state (V15.4) | the conformance rule on static state |
| No secrets committed | `tools/secret-scan.php` and its self-test |

SECURITY.md keeps the threat model, the residual risks and the
supply-chain controls.

## 6. Judgment Calls

| Topic | Decision | Reason |
|---|---|---|
| Base | the September 2026 tarball, with the alphanumeric CNPJ of the March 2026 main line, as 3.0.0 | the tarball is the reviewed design with events and the observer; the main line had the CNPJ and the property-inspector integration; 2.0.0 (2024) is published, so the new line is a major |
| Rule API | `violation()` instead of `getErrorCode()`/`getDefaultMessage()` | §18, and messages that name only the bounds given (ADR-005) |
| Misconfiguration | thrown, with VAL codes | a check that silently passes is a security defect (ADR-004) |
| Attributes | through the kariricode/contract Inspection ports, property-inspector suggested | property-inspector ADR-009 (ADR-003) |
| CNPJ letters | all 26, upper case | RFB Q&A and the DF-e pattern (ADR-008); the main line's I/O/Q exclusion is not in the format |
| `url` | http and https by default | ASVS V1.2.2 (ADR-001, SECURITY.md) |
| IBAN | structure and MOD 97-10 only | a national-length table would need a maintained registry; documented in SPEC-002 |
| Code block | 3200–3299 under VAL, the lowest free block | registry rule 3 |
| Psalm | `NoInterfaceProperties` suppressed | Psalm 6.19 does not model PHP 8.4 interface properties; PHPStan and PHP check them (ADR-007) |
| PER-CS 3.1 | checked with `@PER-CS3.0` | PHP-CS-Fixer 3.95 implements 3.0 (E-046) |

## 7. Mutation Testing

Infection 0.35.5 with `infection.json5`: every default mutator on `src`,
the unit and integration suites, only the test cases that cover each
mutant, a 5 s time-out.

| Run | Since the previous run | Mutants | Escaped | MSI | Covered-code MSI |
|---|---|---|---|---|---|
| 1 | first measurement, after the rework | 1,090 | 34 | 96.88 % | 96.88 % |
| 2 | tests for bound codes, exponents, remainder 2 in CNPJ and PIS, named variadic arguments, unparseable URLs with an allowed scheme, parameter messages; digits read as `ord() − 48`; the field limit checked after an error; redundant casts, `array_values` and key prefixes removed | 1,077 | 3 | 99.72 % | 99.72 % |

Run 2 is this record's. The escaped mutants are equivalent:

| File | Escaped | Why equivalent |
|---|---|---|
| `Core/ValidatorEngine.php` | 2 | the divisor that turns nanoseconds into milliseconds, 1,000,000, changed by one: the duration moves by one part per million |
| `Rule/Financial/LuhnRule.php` | 1 | `$sum += …` → `$sum -= …`: a sum and its negation are both divisible by 10 or both not |

## 8. Open Items

| # | Item | Closes when |
|---|---|---|
| 1 | G3: independent review for P0 findings | a second reviewer signs off |
| 2 | kariricode/exception's main claims VAL 3200–3299; the installed 2.0.2 does not list it, so `CodeBlockRegistryTest` checks that the block is free | a kariricode/exception release ships the row |
| 3 | Release v3.0.0 and Packagist, which still serves 1.0.0 (its webhook missed v1.0.1 and v2.0.0) | the maintainer tags the release and reconnects the webhook |
| 4 | Escaped mutants (§7) | each is killed by a test or stays documented as equivalent |
