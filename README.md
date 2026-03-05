# KaririCode Validator

<div align="center">

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-22c55e.svg)](LICENSE)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-Level%209-4F46E5)](https://phpstan.org/)
[![Rules](https://img.shields.io/badge/Rules-57-22c55e)](https://kariricode.org)
[![Zero Dependencies](https://img.shields.io/badge/Dependencies-0-22c55e)](composer.json)
[![ARFA](https://img.shields.io/badge/ARFA-1.3-orange)](https://kariricode.org)
[![KaririCode Framework](https://img.shields.io/badge/KaririCode-Framework-orange)](https://kariricode.org)

**The most complete PHP 8.4+ validation engine — 57 rules, i18n, zero dependencies.**

[Installation](#installation) · [Quick Start](#quick-start) · [DTO Validation](#attribute-driven-dto-validation) · [All 57 Rules](#all-57-rules) · [Architecture](#architecture)

</div>

---

## The Problem

Every PHP validation library forces a painful choice: zero dependencies and limited rules, or a full ecosystem and 10+ transitive packages:

```php
// Symfony Validator: 7+ symfony/* packages, complex DI requirement
// Respect\Validation: global state, hard to unit-test
// Laravel Validator: 10+ illuminate/* packages, framework-dependent

// None offer: final readonly rules + PHPStan L9 + Brazilian documents
//             + i18n + cross-field validation + collect-all semantics — all at once.
```

## The Solution

```php
use KaririCode\Validator\Provider\ValidatorServiceProvider;

$engine = (new ValidatorServiceProvider())->createEngine();

$result = $engine->validate(
    data: ['email' => 'invalid', 'age' => -5, 'cpf' => '529.982.247-25'],
    fieldRules: [
        'email' => ['required', 'email'],
        'age'   => ['required', 'positive'],
        'cpf'   => ['cpf'],
    ],
);

if (!$result->isValid()) {
    foreach ($result->getErrors() as $error) {
        echo "{$error->field}: [{$error->errorCode}] {$error->message}\n";
    }
}
// email: [string.email] The email must be a valid email address.
// age: [numeric.positive] The age must be a positive number.
// cpf: valid — no error
```

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.4 or higher |
| kariricode/property-inspector | ^2.0 |

---

## Installation

```bash
composer require kariricode/validator
```

---

## Quick Start

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use KaririCode\Validator\Provider\ValidatorServiceProvider;

$engine = (new ValidatorServiceProvider())->createEngine();

$result = $engine->validate(
    data: ['email' => 'invalid-email', 'age' => -5, 'name' => ''],
    fieldRules: [
        'email' => ['required', 'email'],
        'age'   => ['required', 'positive'],
        'name'  => ['required', ['length', ['min' => 2, 'max' => 100]]],
    ],
);

if (!$result->isValid()) {
    foreach ($result->getErrors() as $error) {
        echo "{$error->field}: [{$error->errorCode}] {$error->message}\n";
    }
}
// email: [string.email] The email must be a valid email address.
// age: [numeric.positive] The age must be a positive number.
// name: [string.length] The name must be between 2 and 100 characters.
```

---

## Attribute-Driven DTO Validation

```php
use KaririCode\Validator\Attribute\{Validate, ValidationGroup};

final class CreateUserRequest
{
    public function __construct(
        #[Validate('required', 'email')]
        #[ValidationGroup('create', 'update')]
        public string $email,

        #[Validate('required', ['length', ['min' => 8]])]
        #[ValidationGroup('create')]
        public string $password,

        #[Validate('required', 'cpf')]
        public string $cpf,
    ) {}
}

$validator = (new ValidatorServiceProvider())->createAttributeValidator();
$result    = $validator->validate(new CreateUserRequest(
    email: 'walmir@kariricode.org',
    password: 'secret',
    cpf: '529.982.247-25',
), group: 'create');
```

---

## Comparison with Alternatives

| Feature | Symfony | Respect | Laravel | **KaririCode** |
|---|---|---|---|---|
| External dependencies | 7+ symfony/* | 1 (stringifier) | 10+ illuminate/* | **0** |
| PHP version | 8.2+ | 8.5+ (v3) | 8.2+ | **8.4+** |
| Rules | ~80 | ~150 | ~90 | **57** (52 registered + 5 programmatic) |
| `final readonly` rules | ✗ | ✗ | ✗ | **✓** |
| PHPStan level 9 ready | Partial | ✗ | ✗ | **✓** |
| Brazilian documents | ✗ | ✗ | ✗ | **CPF, CNPJ, CEP, PIS** |
| Cross-field validation | ✓ | ✓ | ✓ | **✓** |
| Attribute-driven DTOs | ✓ | ✗ | ✗ | **✓** |
| Fail-fast + maxErrors | ✗ | ✗ | ✓ (bail) | **✓** |
| i18n (EN, PT-BR, ES) | ✓ | ✗ | ✓ | **✓** |

---

## Cross-Field Validation

```php
$result = $engine->validate(
    data: [
        'password'              => 'secret123',
        'password_confirmation' => 'secret123',
        'backup_email'          => '',
        'primary_email'         => 'user@test.com',
    ],
    fieldRules: [
        'password_confirmation' => [['same_as', ['other' => 'password']]],
        'backup_email'          => [['required_with', ['other' => 'primary_email']]],
    ],
);
```

---

## Brazilian Document Validation

```php
$result = $engine->validate(
    data: ['cpf' => '529.982.247-25', 'cnpj' => '11.222.333/0001-81', 'cep' => '63100-000', 'pis' => '12345678905'],
    fieldRules: ['cpf' => ['cpf'], 'cnpj' => ['cnpj'], 'cep' => ['cep'], 'pis' => ['pis']],
);
```

---

## i18n Messages

```php
use KaririCode\Validator\Core\DefaultMessageResolver;

// Built-in: EN, PT-BR, ES with fallback chain
$resolver = new DefaultMessageResolver('pt_BR');
$engine   = (new ValidatorServiceProvider())->createEngine(messageResolver: $resolver);
// "O campo email é obrigatório." instead of "The email field is required."

// Custom messages
$resolver->addMessage('en', 'logic.required', '{field} cannot be blank!');
$resolver->addMessages('de', [
    'logic.required' => '{field} ist erforderlich.',
    'string.email'   => '{field} muss eine gültige E-Mail sein.',
]);
```

---

## Fail-Fast & Max Errors

```php
use KaririCode\Validator\Configuration\ValidatorConfiguration;

// Stop on first error per field
$config = new ValidatorConfiguration(failFast: true);
$engine = (new ValidatorServiceProvider())->createEngine($config);

// Or limit errors per field
$config = new ValidatorConfiguration(maxErrorsPerField: 2);
$engine = (new ValidatorServiceProvider())->createEngine($config);
```

---

## All 57 Rules

| Category | Rules | Aliases |
|---|---|---|
| **Logic** (8) | Required, Choice, NotNull, Nullable, Callback*, Composite*, Conditional*, AtLeastOneOf* | `required`, `choice`, `not_null`, `nullable` |
| **String** (15) | Email, Url, Length, Pattern, NotEmpty, Alpha, Alphanumeric, Uuid, Ulid, Ip, Json, Hostname, Slug, StartsWith, EndsWith | `email`, `url`, `length`, `pattern`, `not_empty`, `alpha`, `alphanumeric`, `uuid`, `ulid`, `ip`, `json`, `hostname`, `slug`, `starts_with`, `ends_with` |
| **Numeric** (6) | Range, Positive, Integer, Negative, NegativeOrZero, Decimal | `range`, `positive`, `integer`, `negative`, `negative_or_zero`, `decimal` |
| **Comparison** (7) | EqualTo, NotEqualTo, GreaterThan, GreaterThanOrEqual, LessThan, LessThanOrEqual, Between | `equal_to`, `not_equal_to`, `greater_than`, `greater_than_or_equal`, `less_than`, `less_than_or_equal`, `between` |
| **Date** (4) | DateFormat, DateBefore, DateAfter, DateBetween | `date_format`, `date_before`, `date_after`, `date_between` |
| **Type** (2) | Type, InstanceOf | `type`, `instance_of` |
| **Collection** (4) | Count, UniqueElements, KeyExists, Each* | `count`, `unique`, `key_exists` |
| **CrossField** (4) | SameAs, DifferentFrom, RequiredWith, RequiredWithout | `same_as`, `different_from`, `required_with`, `required_without` |
| **Financial** (3) | Luhn, Iban, Bic | `luhn`, `iban`, `bic` |
| **Brazilian** (4) | CPF, CNPJ, CEP, PIS | `cpf`, `cnpj`, `cep`, `pis` |

*\* Require constructor arguments — used programmatically, not via alias.*

---

## Custom Rules

```php
use KaririCode\Validator\Contract\{ValidationRule, ValidationContext};

final readonly class PhoneRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match('/^\+?[\d\s()-]{10,}$/', $value) === 1;
    }

    public function getErrorCode(): string    { return 'custom.phone'; }
    public function getDefaultMessage(): string { return 'The {field} must be a valid phone number.'; }
}

$provider = new ValidatorServiceProvider();
$registry = $provider->createRegistry();
$registry->register('phone', new PhoneRule());
```

---

## Engine API (Programmatic)

```php
$engine = (new ValidatorServiceProvider())->createEngine();

$result = $engine->validate(
    ['email' => 'bad', 'age' => -1],
    ['email' => ['required', 'email'], 'age' => ['positive']],
);

$result->isValid();              // false
$result->errorCount();           // 2
$result->failedFields();         // ['email', 'age']
$result->errorsFor('email');     // [ValidationError{field, errorCode, message, context}]
$result->firstErrorFor('age');   // ValidationError or null

// Merge results from multiple validation passes
$merged = $result1->merge($result2);
```

---

## Ecosystem Position

```
DPO Pipeline:     ★ Validator ★ → Sanitizer → Transformer → Business Logic
Infra Pipeline:   Object ↔ Normalizer ↔ Array ↔ Serializer ↔ String
Cross-Layer:      Request DTO ↔ Mapper ↔ Domain Entity ↔ Mapper ↔ Response DTO
```

The Validator is the **first gate** — it checks correctness before any data processing. Returns structured errors with i18n messages; never mutates input.

---

## Architecture

### Source layout

```
src/
├── Attribute/       Validate · ValidationGroup — DTO annotation support
├── Configuration/   ValidatorConfiguration (immutable, fail-fast, maxErrors)
├── Contract/        ValidationRule · ValidationContext · ValidatorEngine · RuleRegistry
├── Core/            ValidatorEngine · ValidationContextImpl · InMemoryRuleRegistry
│                    DefaultMessageResolver (EN, PT-BR, ES)
├── Exception/       ValidationException · InvalidRuleException
├── Provider/        ValidatorServiceProvider — factory for engine & attribute validator
└── Rule/
    ├── Brazilian/   CPF · CNPJ · CEP · PIS
    ├── Collection/  Count · UniqueElements · KeyExists · Each
    ├── Comparison/  EqualTo · NotEqualTo · GreaterThan … Between
    ├── CrossField/  SameAs · DifferentFrom · RequiredWith · RequiredWithout
    ├── Date/        DateFormat · DateBefore · DateAfter · DateBetween
    ├── Financial/   Luhn · Iban · Bic
    ├── Logic/       Required · Choice · NotNull · Nullable · Callback · Composite …
    ├── Numeric/     Range · Positive · Integer · Negative · NegativeOrZero · Decimal
    ├── String/      Email · Url · Length · Pattern · NotEmpty · Alpha … EndsWith
    └── Type/        Type · InstanceOf
```

### Key design decisions

| Decision | Rationale | ADR |
|---|---|---|
| Collect-all semantics | All errors returned — no fail-fast by default | [ADR-001](docs/adr/ADR-001-collect-all.md) |
| i18n message resolver | Swap language without changing rules | [ADR-002](docs/adr/ADR-002-i18n.md) |
| `final readonly` rules | PHPStan L9, thread safety | [ADR-003](docs/adr/ADR-003-immutable-rules.md) |
| Cross-field context | Rules read sibling fields via ValidationContext | [ADR-004](docs/adr/ADR-004-cross-field.md) |
| Validation groups | Run only relevant subset of rules | [ADR-005](docs/adr/ADR-005-groups.md) |

### Specifications

| Spec | Covers |
|---|---|
| [SPEC-001](docs/spec/SPEC-001-validation-contract.md) | Rule interface and error format |
| [SPEC-002](docs/spec/SPEC-002-message-format.md) | Message template tokens |
| [SPEC-003](docs/spec/SPEC-003-cross-field.md) | Cross-field validation protocol |

---

## Project Stats

| Metric | Value |
|---|---|
| PHP source files | 77 |
| Source lines | 4,203 |
| Test files | 49 |
| Test lines | 4,390 |
| External runtime dependencies | 1 (kariricode/property-inspector) |
| Rule classes | 57 (52 registered + 5 programmatic) |
| Rule categories | 10 |
| i18n languages | 3 (EN, PT-BR, ES) |
| PHPStan level | 9 |
| PHP version | 8.4+ |
| ARFA compliance | 1.3 |

---

## Contributing

```bash
git clone https://github.com/KaririCode-Framework/kariricode-validator.git
cd kariricode-validator
composer install
kcode init
kcode quality  # Must pass before opening a PR
```

---

## License

[MIT License](LICENSE) © [Walmir Silva](mailto:community@kariricode.org)

---

<div align="center">

Part of the **[KaririCode Framework](https://kariricode.org)** ecosystem.

[kariricode.org](https://kariricode.org) · [GitHub](https://github.com/KaririCode-Framework/kariricode-validator) · [Packagist](https://packagist.org/packages/kariricode/validator) · [Issues](https://github.com/KaririCode-Framework/kariricode-validator/issues)

</div>
