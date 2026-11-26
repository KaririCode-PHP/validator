# SPEC-003: #[Validate] Attribute Handler

**Version:** 3.2.0 | **ARFA:** 1.3 V4.0

## 1. Attribute Definition

```php
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
final readonly class Validate
{
    public function __construct(
        public readonly array|string ...$rules,
    ) {}
}
```

## 2. Usage (DPO Pattern)

```php
final readonly class CreateUserInput
{
    public function __construct(
        #[Validate('required', 'email')]
        public string $email,

        #[Validate('required', ['min_length', ['min' => 8]], ['max_length', ['max' => 100]])]
        public string $password,

        #[Validate(['same_as', ['field' => 'password']])]
        public string $passwordConfirm,
    ) {}
}
```

## 3. Handler Lifecycle

```
AttributeValidator::validate($dto)
  │
  ├── PropertyInspector::inspect($dto, ValidateAttributeHandler)
  │       └── Build fieldRules map from #[Validate] metadata
  │
  └── ValidatorEngine::validate($data, $fieldRules)
            └── Return ValidationResult (collect-all)
```
