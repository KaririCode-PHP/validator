# KaririCode Validator

<div align="center">

[![CI](https://github.com/KaririCode-Framework/kariricode-validator/actions/workflows/ci.yml/badge.svg)](https://github.com/KaririCode-Framework/kariricode-validator/actions/workflows/ci.yml)
[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-22c55e.svg)](LICENSE)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-Level%209-4F46E5)](https://phpstan.org/)
[![Psalm](https://img.shields.io/badge/Psalm-Level%201-4F46E5)](https://psalm.dev/)
[![Tests](https://img.shields.io/badge/Tests-100%25_pass-22c55e)](tests/)
[![Coverage](https://img.shields.io/badge/Coverage-100%25-22c55e)](tests/)
[![Rules](https://img.shields.io/badge/Rules-52-22c55e)](docs/spec/SPEC-002-rule-reference.md)
[![ARFA](https://img.shields.io/badge/ARFA-1.3-F97316)](https://kariricode.org)
[![KaririCode Framework](https://img.shields.io/badge/KaririCode-Framework-F97316)](https://kariricode.org)

**Composable, rule-based data validation engine for PHP 8.4+.**  
52 rules · collect-all errors · i18n messages · #[Validate] attributes · zero dependencies.

[Installation](#installation) · [Quick Start](#quick-start) · [Attribute API](#attribute-driven-dto-validation) · [All 52 Rules](#all-52-rules) · [CI Integration](#ci-integration) · [Architecture](#architecture)

</div>

---

## The Problem

Validation logic leaks into every layer — controllers, services, repositories — with no consistent contract and no complete error feedback:

```php
// Scattered, inconsistent, and fail-fast (single error at a time)
if (empty($data['email'])) {
    throw new \InvalidArgumentException('Email is required.');
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    throw new \InvalidArgumentException('Email is invalid.');
}
// User fixes email, submits again → discovers password is also wrong
// ← 2 round-trips to see 2 errors
```

## The Solution

```php
use KaririCode\Validator\Provider\ValidatorServiceProvider;

$validator = (new ValidatorServiceProvider())->createEngine();

$result = $validator->validate(
    data: ['email' => 'not-an-email', 'password' => 'abc', 'cpf' => '000.000.000-00'],
    rules: [
        'email'    => ['required', 'email'],
        'password' => ['required', ['min_length', ['min' => 8]]],
        'cpf'      => ['cpf'],
    ],
);

// ALL errors collected in ONE pass
$result->isValid();       // false
$result->getErrors();
// [
//   'email'    => [['rule' => 'email',      'message' => 'Must be a valid email address.']],
//   'password' => [['rule' => 'min_length', 'message' => 'Must be at least 8 characters.']],
//   'cpf'      => [['rule' => 'cpf',        'message' => 'Must be a valid CPF number.']],
// ]
```

---

## Features

- ✅ **52 built-in rules** across 8 categories (Required, String, Numeric, Date, File, Collection, Brazilian, Cross-Field)
- ✅ **Collect-all semantics** — ALL errors for ALL fields in a single pass (ADR-002)
- ✅ **i18n error messages** — locale-aware message keys; swap message catalogue per project
- ✅ **Attribute-driven API** — `#[Validate]` on DPO properties; zero boilerplate
- ✅ **Cross-field validation** — `same_as`, `different_from` via `ValidationContext`
- ✅ **Brazilian rules** — CPF, CNPJ, CEP, phone with digit verification
- ✅ **Zero external dependencies** — only `kariricode/property-inspector` for reflection
- ✅ **PHPStan Level 9 + Psalm Level 1** — full static-analysis compliance
- ✅ **100% test coverage** — 100% Classes/Methods/Lines
- ✅ **ARFA 1.3 compliant** — `final readonly` DPO pattern, immutable context

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.4+ |
| Composer | 2.x |
| kariricode/property-inspector | ^2.0 |

---

## Installation

```bash
composer require kariricode/validator
```

---

## Quick Start

### 1. Engine API

```php
use KaririCode\Validator\Provider\ValidatorServiceProvider;

$validator = (new ValidatorServiceProvider())->createEngine();

$result = $validator->validate(
    data:  ['age' => -5, 'email' => 'not-an-email'],
    rules: ['age' => ['required', 'integer', 'positive'], 'email' => ['required', 'email']],
);

if (!$result->isValid()) {
    return response()->json(['errors' => $result->getErrors()], 422);
}
```

### 2. Attribute API (DPO Pattern, ARFA 1.3)

```php
use KaririCode\Validator\Attribute\Validate;

final readonly class CreateUserInput
{
    public function __construct(
        #[Validate('required', 'email')]
        public string $email,

        #[Validate('required', ['min_length', ['min' => 8]], ['max_length', ['max' => 128]])]
        public string $password,

        #[Validate(['same_as', ['field' => 'password']])]
        public string $passwordConfirm,

        #[Validate('required', 'cpf')]
        public string $cpf,
    ) {}
}

$provider  = new ValidatorServiceProvider();
$validator = $provider->createAttributeValidator($provider->createConfiguration());

$input  = new CreateUserInput($email, $password, $confirm, $cpf);
$result = $validator->validate($input);
```

---

## Real-World Use Cases

### Use Case 1 — User Registration DPO

```php
final readonly class RegisterInput
{
    #[Validate('required', 'email')]
    public string $email;

    #[Validate('required', ['min_length', ['min' => 8]])]
    public string $password;

    #[Validate(['same_as', ['field' => 'password']])]
    public string $passwordConfirm;

    #[Validate('required', 'cpf')]
    public string $cpf;

    #[Validate('required', ['min_length', ['min' => 3]], ['max_length', ['max' => 100]])]
    public string $fullName;
}
```

### Use Case 2 — E-commerce Order Validation

```php
$result = $validator->validate(
    data: $orderPayload,
    rules: [
        'customer.email'        => ['required', 'email'],
        'customer.cpf'          => ['required', 'cpf'],
        'shipping.cep'          => ['required', 'cep'],
        'items'                 => ['required', ['count', ['min' => 1, 'max' => 100]]],
        'payment.amount'        => ['required', 'float', 'positive'],
        'payment.installments'  => [['integer'], ['range', ['min' => 1, 'max' => 12]]],
    ],
);
```

### Use Case 3 — File Upload Validation

```php
$result = $validator->validate(
    data: ['avatar' => $uploadedFile, 'document' => $pdfFile],
    rules: [
        'avatar'   => ['required', 'image', ['max_size', ['max' => 2_000_000]]],
        'document' => ['required', 'file',  ['max_size', ['max' => 10_000_000]]],
    ],
);
```

### Use Case 4 — REST API Payload Validation (nested)

```php
$result = $validator->validate(
    data: $request->json(),
    rules: [
        'product.name'         => ['required', ['max_length', ['max' => 200]]],
        'product.sku'          => ['required', 'alpha_num', 'uppercase'],
        'product.price'        => ['required', 'float', 'positive'],
        'product.category_id'  => ['required', 'integer', 'positive'],
        'product.description'  => [['max_length', ['max' => 2000]]],
    ],
);

if (!$result->isValid()) {
    return json_encode(['errors' => $result->getErrors()], JSON_UNESCAPED_UNICODE);
}
```

---

## All 52 Rules

### Required / Optional
| Alias | Description |
|-------|-------------|
| `required` | Field must be non-null and non-empty |
| `optional` | Skip validation if field is absent |
| `not_blank` | Must not be whitespace-only |
| `nullable` | Allows null; skip other rules if null |

### String (12 rules)
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `email` | — | RFC 5322 email |
| `url` | — | Valid URL |
| `ip` | `version: 4\|6` | Valid IP address |
| `uuid` | — | Valid UUID |
| `min_length` | `min: int` | Minimum character count |
| `max_length` | `max: int` | Maximum character count |
| `length` | `min`, `max` | Character count range |
| `pattern` | `pattern: string` | Regex match |
| `alpha` | — | Letters only |
| `alpha_num` | — | Letters and numbers |
| `slug` | — | URL-safe slug |
| `json` | — | Valid JSON string |

### Numeric (7 rules)
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `integer` | — | Must be integer type/string |
| `float` | — | Must be float type/string |
| `min` | `min: float` | Minimum numeric value |
| `max` | `max: float` | Maximum numeric value |
| `range` | `min`, `max` | Numeric value range |
| `positive` | — | Greater than zero |
| `negative` | — | Less than zero |

### Date (4 rules)
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `date` | `format: string` | Valid date in format |
| `date_range` | `from`, `to` | Date within range |
| `before` | `date: string` | Must be before date |
| `after` | `date: string` | Must be after date |

### File (3 rules)
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `file` | — | Valid uploaded file |
| `image` | `types: list<string>` | Image MIME type allowed |
| `max_size` | `max: int` (bytes) | Maximum file size |

### Collection (3 rules)
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `each` | `rules: list` | Apply rules to every element |
| `count` | `min`, `max` | Array element count |
| `unique` | — | All elements unique |

### Brazilian (4 rules)
| Alias | Description |
|-------|-------------|
| `cpf` | Valid CPF (with check digit verification) |
| `cnpj` | Valid CNPJ |
| `cep` | Valid CEP (Brazilian postal code) |
| `phone_br` | Valid Brazilian phone number |

### Cross-Field (2 rules)
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `same_as` | `field: string` | Must match another field |
| `different_from` | `field: string` | Must differ from another field |

---

## CI Integration

```yaml
- name: Run quality pipeline
  run: |
    kcode init
    sed -i 's/beStrictAboutCoverageMetadata="true"/beStrictAboutCoverageMetadata="false"/' .kcode/phpunit.xml.dist
    kcode quality
```

| Tool | Level | Result |
|------|-------|--------|
| `php-cs-fixer` | KaririCode standard | ✅ |
| `phpstan` | Level 9 | ✅ |
| `psalm` | Level 1 | ✅ |
| `phpunit` + `pcov` | 100% coverage | ✅ |

---

## Architecture

### Design Decisions

| Decision | Rationale | ADR |
|----------|-----------|-----|
| Alias-based rule registry | Zero coupling between engine and rule classes | [ADR-001](docs/adr/ADR-001-rule-registry-pattern.md) |
| Collect-all error semantics | Single-pass; expose ALL errors to give users complete feedback | [ADR-002](docs/adr/ADR-002-collect-all-semantics.md) |
| `ValidationContext` immutability | `final readonly` + builder pattern; no cross-field pollution | [ADR-003](docs/adr/ADR-003-validation-context-immutability.md) |
| i18n via message key catalogue | Zero third-party i18n dep; pluggable message files | [ADR-004](docs/adr/ADR-004-i18n-error-messages.md) |
| Cross-field via `$context->getData()` | Simple contract; no engine special-casing required | [ADR-005](docs/adr/ADR-005-cross-field-validation.md) |

### Project Stats

| Metric | Value |
|--------|-------|
| Rule classes | 52 |
| External dependencies | 1 (`kariricode/property-inspector`) |
| Line coverage | 100% |
| PHPStan level | 9 (0 errors) |
| Psalm level | 1 (0 errors) |

---

## Ecosystem Integration

| Library | Integration |
|---------|-------------|
| [kariricode/sanitizer](https://github.com/KaririCode-Framework/kariricode-sanitizer) | Sanitize before validating (trim whitespace, normalise case) |
| [kariricode/transformer](https://github.com/KaririCode-Framework/kariricode-transformer) | Transform after validation passes (e.g., slug, CPF mask) |
| [kariricode/normalizer](https://github.com/KaririCode-Framework/kariricode-normalizer) | Normalize input arrays before validation |
| [kariricode/property-inspector](https://github.com/KaririCode-Framework/kariricode-property-inspector) | Reflection caching for `#[Validate]` attribute scanning |
| [kariricode/devkit](https://github.com/KaririCode-Framework/kariricode-devkit) | `kcode quality` CI pipeline runner |

---

## Contributing

1. Fork the repository  
2. Create a feature branch: `git checkout -b feat/my-validation-rule`  
3. Run: `kcode quality` — all 4 tools must pass  
4. Submit a pull request against `develop`

---

<div align="center">

**Part of the [KaririCode Framework](https://kariricode.org) ecosystem.**

[![GitHub](https://img.shields.io/badge/GitHub-KaririCode-181717?logo=github)](https://github.com/KaririCode-Framework)
[![Packagist](https://img.shields.io/badge/Packagist-kariricode%2Fvalidator-F28D1A?logo=packagist&logoColor=white)](https://packagist.org/packages/kariricode/validator)

*Built with ❤️ by [Walmir Silva](https://github.com/walmir-silva) · [kariricode.org](https://kariricode.org)*

</div>
