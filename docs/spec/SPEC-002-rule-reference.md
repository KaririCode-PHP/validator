# SPEC-002: Rule Reference (52 rules)

**Version:** 3.1.0 | **ARFA:** 1.3 V4.0

## Required / Optional
| Alias | Description |
|-------|-------------|
| `required` | Field must be non-null, non-empty |
| `optional` | Skip validation if field is missing |
| `not_blank` | Must not be blank (whitespace-only) |

## String Rules
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `email` | — | RFC 5322 email format |
| `url` | — | Valid URL |
| `ip` | `version: 4|6` | Valid IP address |
| `uuid` | — | Valid UUID v4 |
| `min_length` | `min: int` | Minimum string length |
| `max_length` | `max: int` | Maximum string length |
| `length` | `min`, `max` | Length range |
| `pattern` | `pattern: string` | Regex match |
| `alpha` | — | Letters only |
| `alpha_num` | — | Letters and numbers |
| `slug` | — | URL-safe slug |

## Numeric Rules
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `integer` | — | Must be integer |
| `float` | — | Must be float |
| `min` | `min: float` | Minimum value |
| `max` | `max: float` | Maximum value |
| `range` | `min`, `max` | Value range |
| `positive` | — | Greater than zero |
| `negative` | — | Less than zero |

## Date Rules
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `date` | `format: string` | Valid date |
| `date_range` | `from`, `to` | Date within range |
| `before` | `date: string` | Must be before |
| `after` | `date: string` | Must be after |

## File Rules
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `file` | — | Valid uploaded file |
| `image` | `types: list` | Image MIME type |
| `max_size` | `max: int` (bytes) | Max file size |

## Collection Rules
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `each` | `rules: list` | Apply rules to each element |
| `count` | `min`, `max` | Array count range |
| `unique` | — | All elements unique |

## Brazilian Rules
| Alias | Description |
|-------|-------------|
| `cpf` | Valid CPF (with digit verification) |
| `cnpj` | Valid CNPJ |
| `cep` | Valid CEP (postal code) |
| `phone_br` | Valid Brazilian phone number |

## Cross-Field Rules
| Alias | Parameters | Description |
|-------|-----------|-------------|
| `same_as` | `field: string` | Must match another field |
| `different_from` | `field: string` | Must differ from another field |
