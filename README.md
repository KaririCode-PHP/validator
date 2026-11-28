# KaririCode Validator

[![CI](https://github.com/KaririCode-PHP/validator/actions/workflows/ci.yml/badge.svg)](https://github.com/KaririCode-PHP/validator/actions/workflows/ci.yml)
![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4)
![ARFA 1.3 level 1](https://img.shields.io/badge/ARFA-1.3%20level%201-blue)
![License MIT](https://img.shields.io/badge/license-MIT-green)

Rule-based data validation for PHP 8.4: 57 built-in rules, from strings
and numbers to IBANs and Brazilian documents (the alphanumeric CNPJ
included), applied to arrays by a rule map or to objects by `#[Validate]`
attributes. Every failing rule of every field is reported, with a
message in English, Portuguese or Spanish; a rule written down wrongly
is the application's mistake and throws a coded exception instead of
passing or failing silently. Part of the KaririCode Framework; Spec
V4.3.3, ARFA 1.3 level 1.

## 1. Features

- **57 rules** in ten groups: logic, strings, numbers, comparisons,
  dates, types, collections, cross-field, financial, Brazilian
  documents (SPEC-002).
- **Collect-all results** — every error of every field, in order;
  `failFast` and `maxErrorsPerField` cut it short per field (ADR-002).
- **Rule maps** — an alias (`'email'`), a rule instance, or
  `[alias or rule, parameters]`; fields by dotted path
  (`'address.city'`).
- **Attributes** — `#[Validate]` and `#[ValidationGroup]` on a DTO,
  read through the kariricode/contract `PropertyInspector`, which
  kariricode/property-inspector 3 implements (ADR-003).
- **Messages** — a catalog in en, pt_BR and es, custom messages per
  locale, a pluggable `MessageResolver`; a bound the rule applied by
  default still appears in the message (ADR-005).
- **Strict parameters** — a missing bound, a misspelled type, a pattern
  that does not compile throw `InvalidRuleException` with a VAL code
  (ADR-004).
- **Integrations** — a kariricode/contract `Processor` and `Validator`
  for pipelines, a `ServiceProvider` for containers, an observer that
  receives start and completion events (ADR-006).
- **No dependencies** beyond kariricode/contract and
  kariricode/exception.

## 2. Installation

```bash
composer require kariricode/validator
composer require kariricode/property-inspector   # only for #[Validate] on objects
```

PHP 8.4 or later.

## 3. Quick Start

```php
<?php

declare(strict_types=1);

use KaririCode\Validator\Core\ValidatorEngine;

require __DIR__ . '/vendor/autoload.php';

$engine = new ValidatorEngine();

$result = $engine->validate(
    ['email' => 'ana@example', 'password' => 'secret', 'cnpj' => '12.ABC.345/01DE-35', 'nickname' => null],
    [
        'email' => ['required', 'email'],
        'password' => ['required', ['length', ['min' => 12]]],
        'cnpj' => ['required', 'cnpj'],
        'nickname' => ['nullable', ['length', ['max' => 20]]],
    ],
);

echo json_encode($result->messages(), JSON_PRETTY_PRINT), PHP_EOL;
```

```json
{
    "email": [
        "The email must be a valid email address."
    ],
    "password": [
        "The password must be at least 12 characters."
    ]
}
```

`cnpj` passes (it is the Receita Federal's example of an alphanumeric
CNPJ) and `nickname` is null, so 'nullable' skips its other rules.

## 4. Rule Maps

A rule map lists, per field, the rule definitions to apply in order:

| Definition | Example |
|---|---|
| alias | `'email'` |
| rule instance | `new EachRule(new EmailRule())` |
| alias with parameters | `['length', ['min' => 2, 'max' => 100]]` |
| rule with parameters | `[new CompositeRule('custom.code', 'The {field} is invalid.', new LengthRule(), new PatternRule()), ['max' => 8, 'pattern' => '/\A\d+\z/']]` |

Field names are dotted paths into nested arrays; a key that contains
dots itself is matched first. A missing field reads as null.

Register your own rules under an alias:

```php
$engine->registry->register('even', new CallbackRule(
    static fn (mixed $value): bool => is_int($value) && $value % 2 === 0,
    errorCode: 'custom.even',
    message: 'The {field} must be even.',
));
```

## 5. The Rules

| Group | Aliases |
|---|---|
| Logic | required, not_null, nullable, choice; and the rule classes CallbackRule, CompositeRule, ConditionalRule, AtLeastOneOfRule |
| String | email, url, length, pattern, not_empty, alpha, alphanumeric, uuid, ulid, ip, json, hostname, slug, starts_with, ends_with |
| Numeric | integer, decimal, positive, negative, negative_or_zero, range |
| Comparison | equal_to, not_equal_to, greater_than, greater_than_or_equal, less_than, less_than_or_equal, between |
| Date | date_format, date_before, date_after, date_between |
| Type | type, instance_of |
| Collection | count, unique, key_exists; and EachRule |
| Cross-field | same_as, different_from, required_with, required_without |
| Financial | luhn, iban, bic |
| Brazilian | cpf, cnpj, cep, pis |

[SPEC-002](docs/spec/SPEC-002-rule-reference.md) lists each rule's
parameters and what it accepts. Points worth knowing:

- `url` accepts http and https unless `schemes` says otherwise, so
  `javascript:` and `file:` URLs fail.
- `range` is inclusive, `between` exclusive; integers are compared as
  integers.
- `cnpj` accepts numeric and alphanumeric CNPJs, all 26 letters, upper
  case, bare or formatted (ADR-008); `cpf`, `cep` and `pis` accept
  bare or formatted digits and nothing else.
- Date rules zero the fields the format does not name and reject dates
  PHP would roll over (February 30); bounds may be strings in the
  format or `DateTimeInterface` objects.

## 6. Attributes

```php
use KaririCode\PropertyInspector\Core\ReflectionPropertyInspector;
use KaririCode\Validator\Attribute\Validate;
use KaririCode\Validator\Attribute\ValidationGroup;
use KaririCode\Validator\Core\AttributeValidator;

final readonly class SignUp
{
    public function __construct(
        #[Validate('required', 'email')]
        public string $email,
        #[Validate(['length', ['min' => 12]])]
        public string $password,
        #[Validate(['same_as', ['other' => 'password']])]
        public string $passwordConfirmation,
        #[Validate('required', 'uuid')]
        #[ValidationGroup('update')]
        public ?string $id = null,
    ) {}
}

$validator = new AttributeValidator($engine, new ReflectionPropertyInspector());
$result = $validator->validate($signUp, 'create');   // skips $id
```

`#[Validate]` takes positional definitions only; named arguments throw.
A property without `#[ValidationGroup]` is validated in every group.
Cross-field rules see the properties that carry `#[Validate]`.

## 7. Results and Messages

`ValidationResult` holds `ValidationError`s (field, error code, message,
parameters) in the order the rules ran: `isValid()`, `errorsFor()`,
`firstErrorFor()`, `failedFields()`, `messages()`, `merge()`, `with()`.

```php
$engine = new ValidatorEngine(configuration: new ValidatorConfiguration(
    defaultLocale: 'pt_BR',
    failFast: true,
    customMessages: ['pt_BR' => ['custom.even' => 'O campo {field} deve ser par.']],
));
```

Messages resolve in the configured locale, then in English, then to the
rule's own message. Placeholders take the rule's parameters and the
field name; a value the rule applied by default (`decimal`'s two places)
is filled in too.

## 8. Exceptions

Invalid input is reported in the result. Exceptions are for mistakes in
the rules and for callers that asked for one; every exception
implements `ValidatorException` and carries a code from VAL 3200–3299:

| Code | Exception | Family | Meaning |
|---|---|---|---|
| 3201–3206 | InvalidRuleException | Configuration | duplicate or unknown alias, malformed definition, missing or invalid parameter, named attribute arguments |
| 3220–3221 | ConfigurationException | Configuration | a setting out of range, a container binding of the wrong type |
| 3230 | ValidationException | Validation | input failed where an exception was asked for (it carries the result) |
| 3240 | UnsupportedInputException | Validation | the processor received something other than an array |

## 9. Integrations

```php
// kariricode/processor-pipeline, or anything that takes a contract Processor
$pipeline->addProcessor(new ValidationProcessor($engine, ['email' => ['required', 'email']]));

// a kariricode/contract container
(new ValidatorServiceProvider(new ValidatorConfiguration(defaultLocale: 'pt_BR')))->register($container);

// events, for logs and metrics
$engine = new ValidatorEngine(observer: static fn (object $event) => $dispatcher->dispatch($event));
```

The processor returns valid input unchanged and throws
`ValidationException` otherwise. The provider binds the registry, the
message resolver, the engine and the `AttributeValidator` (bind a
`PropertyInspector` for it). The observer receives a
`ValidationStartedEvent` (field names, group) and a
`ValidationCompletedEvent` (result, duration in ms); neither carries
values.

## 10. Security

Validation is an input control: run it before the data reaches anything
that trusts it, and validate on the server. Messages are plain text;
escape them where you render HTML. See [SECURITY.md](SECURITY.md) for
the threat model and the ASVS 5.0 mapping.

## 11. Architecture and Quality

| Gate | Result |
|---|---|
| Tests | unit, integration and conformance suites; 100 % of lines covered |
| Static analysis | PHPStan level max (strict and PHPUnit rules), Psalm error level 1 |
| Mutation testing | MSI recorded in [QD-V433](docs/quality/QD-V433-COMPLIANCE.md) |
| Complexity | cyclomatic ≤ 10, nesting ≤ 3, parameters ≤ 4, cognitive ≤ 15 |

The gates run through KaririCode Devkit (`kcode`); see
[CONTRIBUTING.md](CONTRIBUTING.md). Design decisions are in
[docs/adr](docs/adr), the specification in [docs/spec](docs/spec).

## 12. License

MIT — see [LICENSE](LICENSE).
