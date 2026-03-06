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

## Quality Gate (kcode DevKit)

This component uses `kcode` (KaririCode DevKit global binary) for all quality checks.
See [ARFA Spec V4.0 §16](https://github.com/KaririCode-Framework/kariricode-devkit) for the full infrastructure standard.

### Bootstrap (once per machine/CI)

```bash
# Install kcode globally
curl -L https://github.com/KaririCode-Framework/kariricode-devkit/releases/latest/download/kcode.phar \
     -o /usr/local/bin/kcode && chmod +x /usr/local/bin/kcode

# Bootstrap project toolchain
kcode init
```

### Common Commands

| Command | Description |
|---------|-------------|
| `kcode quality` | Full pipeline: cs-fix → analyse → test |
| `kcode test` | PHPUnit + pcov coverage |
| `kcode analyse` | PHPStan L9 + Psalm L1 |
| `kcode cs:fix` | Apply KaririCode code style |
| `kcode cs:fix --check` | Dry-run style check |
| `kcode security` | `composer audit` vulnerability scan |

### Internal Tool Direct Access

After `kcode init`, tools are under `.kcode/vendor/bin/`:

```bash
# PHPStan with custom flags
.kcode/vendor/bin/phpstan analyse --level=9 --configuration=.kcode/phpstan.neon src/

# Psalm — auto-add #[Override]
.kcode/vendor/bin/psalm --config=.kcode/psalm.xml --alter --issues=MissingOverrideAttribute

# php-cs-fixer — preview diff
.kcode/vendor/bin/php-cs-fixer fix --config=.kcode/php-cs-fixer.php --dry-run --diff

# PHPUnit — single test filter
.kcode/vendor/bin/phpunit --configuration=.kcode/phpunit.xml.dist --filter=testMyMethod
```
