# SPEC-001: Validator Engine

**Version:** 3.1.0 | **ARFA:** 1.3 V4.0

## 1. Engine Contract

```php
final class ValidatorEngine
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, list<ValidationRule|string|array<mixed>>> $fieldRules
     */
    public function validate(array $data, array $fieldRules): ValidationResult;
}
```

## 2. Validate Flow (Collect-All)

```
Initialize ValidationResult
For each field in $fieldRules:
  1. resolveValue($data, $field)
  2. For each rule definition:
     a. resolveRule($def) → (ValidationRule, params[])
     b. Build ValidationContextImpl(data, field, params)
     c. if rule->validate($value, $context) === false:
           result->addError($field, $rule->getName(), $rule->getErrorKey())
  ← Never stop early; collect all errors
Return ValidationResult
```

## 3. Result

`ValidationResult` exposes:
- `isValid(): bool` — true if no errors
- `getErrors(): array<string, list<ValidationError>>` — field → errors
- `hasErrors(string $field): bool`
- `getErrorsForField(string $field): list<ValidationError>`
