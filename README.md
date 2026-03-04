# KaririCode\Validator

**The most complete PHP 8.4+ validation engine — 57 rules, zero dependencies.**

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![ARFA](https://img.shields.io/badge/ARFA-1.3-orange)]()
[![Rules](https://img.shields.io/badge/rules-57-brightgreen)]()

Part of the [KaririCode Framework](https://github.com/kariricode) processing ecosystem.

## Why KaririCode\Validator

| Feature | Symfony | Respect | Laravel | **KaririCode** |
|---------|---------|---------|---------|----------------|
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

## Installation

```bash
composer require kariricode/validator
```

## Quick Start

```php
use KaririCode\Validator\Provider\ValidatorServiceProvider;

$engine = (new ValidatorServiceProvider())->createEngine();

$result = $engine->validate(
    data: [
        'email' => 'invalid-email',
        'age'   => -5,
        'name'  => '',
    ],
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
$result = $validator->validate(new CreateUserRequest(
    email: 'walmir@kariricode.org',
    password: 'secret',
    cpf: '529.982.247-25',
), group: 'create');
```

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

## Brazilian Document Validation

```php
$result = $engine->validate(
    data: [
        'cpf'  => '529.982.247-25',
        'cnpj' => '11.222.333/0001-81',
        'cep'  => '63100-000',
        'pis'  => '12345678905',
    ],
    fieldRules: [
        'cpf'  => ['cpf'],
        'cnpj' => ['cnpj'],
        'cep'  => ['cep'],
        'pis'  => ['pis'],
    ],
);
```

## Financial Validation

```php
$result = $engine->validate(
    data: [
        'card' => '4532015112830366',
        'iban' => 'GB29NWBK60161331926819',
        'bic'  => 'NWBKGB2L',
    ],
    fieldRules: [
        'card' => ['luhn'],
        'iban' => ['iban'],
        'bic'  => ['bic'],
    ],
);
```

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

## i18n Messages

```php
use KaririCode\Validator\Core\DefaultMessageResolver;

// Built-in: EN, PT-BR, ES with fallback chain
$resolver = new DefaultMessageResolver('pt_BR');
$engine = (new ValidatorServiceProvider())->createEngine(
    messageResolver: $resolver,
);
// "O campo email é obrigatório." instead of "The email field is required."

// Custom messages
$resolver->addMessage('en', 'logic.required', '{field} cannot be blank!');
$resolver->addMessages('de', [
    'logic.required' => '{field} ist erforderlich.',
    'string.email'   => '{field} muss eine gültige E-Mail sein.',
]);
```

## All 57 Rules

| Category | Rules | Aliases |
|----------|-------|---------|
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

## Custom Rules

```php
use KaririCode\Validator\Contract\{ValidationRule, ValidationContext};

final readonly class PhoneRule implements ValidationRule
{
    public function validate(mixed $value, ValidationContext $context): bool
    {
        return is_string($value) && preg_match('/^\+?[\d\s()-]{10,}$/', $value) === 1;
    }

    public function getErrorCode(): string { return 'custom.phone'; }
    public function getDefaultMessage(): string { return 'The {field} must be a valid phone number.'; }
}

// Register
$provider = new ValidatorServiceProvider();
$registry = $provider->createRegistry();
$registry->register('phone', new PhoneRule());
```

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
$result1 = $engine->validate($data, $rules1);
$result2 = $engine->validate($data, $rules2);
$merged = $result1->merge($result2);
```

## Ecosystem Position

```
DPO Pipeline:     ★ Validator ★ → Sanitizer → Transformer → Business Logic
Infra Pipeline:   Object ↔ Normalizer ↔ Array ↔ Serializer ↔ String
Cross-Layer:      Request DTO ↔ Mapper ↔ Domain Entity ↔ Mapper ↔ Response DTO
```

The Validator is the **first gate** — it checks correctness before any data processing. Returns structured errors with i18n messages; never mutates input.

## Architecture

- ARFA 1.3 compliant (immutable context, reactive pipeline, observability events)
- Quality Directive V4.0 (all rules `final readonly`, zero dependencies)
- See [docs/](docs/) for 5 ADRs, 3 SPECs, and compliance report

## Metrics

| Metric | Value |
|--------|-------|
| Source files | 77 |
| Source lines | 4,203 |
| Test files | 49 |
| Test lines | 4,390 |
| Total | **126 files / 8,593 lines** |
| Rule classes | 57 (52 registered + 5 programmatic) |
| Rule categories | 10 |
| External dependencies | **0** |

## License

MIT © Walmir Silva — KaririCode Framework
