# SPEC-002: KaririCode\Validator 3.0 — Rule Reference

- **Version:** 3.0.0
- **Rules:** 57; 52 registered by alias in `InMemoryRuleRegistry::withDefaults()`, 5 built in code

Every rule returns false for value types it does not handle and reads
its parameters before the value; a missing parameter throws VAL_3204, a
parameter of the wrong type VAL_3205 (SPEC-001 §4). "Bare or formatted"
means each separator is optional. Error codes are the keys of
`MessageCatalog` (en, pt_BR, es).

## 1. Logic

| Alias / class | Parameters | Passes | Code |
|---|---|---|---|
| required | — | not null, not a whitespace-only string, not `[]`; 0, "0" and false pass | logic.required |
| not_null | — | anything but null | logic.not_null |
| nullable | — | always; when the value is null the engine skips the field's other rules | logic.nullable |
| choice | `choices` (non-empty list) | `in_array($value, $choices, true)` | logic.choice |
| CallbackRule | closure, `errorCode`, `message` | the closure returns exactly true | the given code |
| CompositeRule | `errorCode`, `message`, ≥ 1 rule | every rule passes; one error | the given code |
| ConditionalRule | condition closure, inner rule | the condition is not true, or the inner rule passes | the inner rule's |
| AtLeastOneOfRule | ≥ 1 rule | any rule passes | logic.at_least_one_of |

Composites with no rule throw VAL_3203 when built.

## 2. String

| Alias | Parameters | Passes | Code |
|---|---|---|---|
| email | — | FILTER_VALIDATE_EMAIL (≤ 254 characters, ASCII local part); syntax only | string.email |
| url | `schemes` (list, default http, https; case-insensitive) | FILTER_VALIDATE_URL and an allowed scheme | string.url |
| length | `min`, `max` (int, at least one) | characters (UTF-8) within the inclusive bounds | string.length, string.length_min, string.length_max |
| pattern | `pattern` (PCRE with delimiters) | the pattern matches; a pattern that does not compile throws VAL_3205; an input the engine cannot match fails | string.pattern |
| not_empty | — | a string with a non-whitespace character | string.not_empty |
| alpha | — | `\A\pL+\z` (UTF-8) | string.alpha |
| alphanumeric | — | `\A[\pL\pN]+\z` (UTF-8) | string.alphanumeric |
| uuid | — | 8-4-4-4-12 hex digits, any case, any version | string.uuid |
| ulid | — | 26 Crockford base32 characters, any case, first ≤ 7 | string.ulid |
| ip | `version` (4 or 6, optional) | FILTER_VALIDATE_IP | string.ip |
| json | — | `json_validate()` | string.json |
| hostname | — | labels of 1–63 `[A-Za-z0-9-]`, no hyphen at either end, ≤ 253 characters | string.hostname |
| slug | — | `\A[a-z0-9]+(?:-[a-z0-9]+)*\z` | string.slug |
| starts_with | `prefix` (non-empty) | `str_starts_with()`, case-sensitive | string.starts_with |
| ends_with | `suffix` (non-empty) | `str_ends_with()`, case-sensitive | string.ends_with |

Every pattern anchors with `\A` and `\z`: a trailing newline fails.

## 3. Numeric

A number is an int, a finite float or a numeric string; booleans, INF
and NAN are not (`Core\NumericValue`). Integers are compared as
integers.

| Alias | Parameters | Passes | Code |
|---|---|---|---|
| integer | — | FILTER_VALIDATE_INT: ints, integral floats, decimal strings with optional sign and surrounding whitespace; not booleans, "012", "1e3" | numeric.integer |
| decimal | `places` (int ≥ 0, default 2) | at most `places` decimals as written, trailing zeros ignored, exponents applied; floats by their shortest exact representation | numeric.decimal |
| positive | — | > 0 | numeric.positive |
| negative | — | < 0 | numeric.negative |
| negative_or_zero | — | ≤ 0 | numeric.negative_or_zero |
| range | `min`, `max` (numbers, at least one) | inclusive bounds | numeric.range, numeric.range_min, numeric.range_max |

## 4. Date

The value is a string in `format` (default `Y-m-d`) or a
`DateTimeInterface`. Parsing zeroes the fields the format does not name
and rejects dates PHP would roll over (`Core\DateValue`). Bounds are
strings in the same format or `DateTimeInterface` objects; anything else
throws VAL_3205. Dates compare as instants.

| Alias | Parameters | Passes | Code |
|---|---|---|---|
| date_format | `format` | parses and prints back identically | date.format |
| date_before | `before`, `format` | strictly earlier | date.before |
| date_after | `after`, `format` | strictly later | date.after |
| date_between | `after`, `before`, `format` | strictly between | date.between |

## 5. Comparison

| Alias | Parameters | Passes | Code |
|---|---|---|---|
| equal_to | `expected` (any, null included) | `===` | comparison.equal_to |
| not_equal_to | `expected` | `!==` | comparison.not_equal_to |
| greater_than | `threshold` (number) | number > threshold | comparison.greater_than |
| greater_than_or_equal | `threshold` | ≥ | comparison.greater_than_or_equal |
| less_than | `threshold` | < | comparison.less_than |
| less_than_or_equal | `threshold` | ≤ | comparison.less_than_or_equal |
| between | `min`, `max` (numbers) | exclusive bounds | comparison.between |

## 6. Type

| Alias | Parameters | Passes | Code |
|---|---|---|---|
| type | `type`: string, int/integer, float/double, bool/boolean, array, object, null, numeric, scalar, callable | the matching `is_*` function; an unknown name throws | type.mismatch |
| instance_of | `class` (existing class, interface or enum) | `instanceof`; a missing class throws | type.instance_of |

## 7. Collection

| Alias / class | Parameters | Passes | Code |
|---|---|---|---|
| count | `min`, `max` (int, at least one) | an array or Countable within inclusive bounds | collection.count, collection.count_min, collection.count_max |
| unique | — | no two identical items: scalars and arrays by type and value, objects by identity | collection.unique |
| key_exists | `key` (int or string) | `array_key_exists()` | collection.key_exists |
| EachRule | inner rule | an array whose every item passes; `[]` passes | collection.each |

## 8. Cross-field

`other` names a field by path (SPEC-001 §4). "Filled in" means what
`required` accepts.

| Alias | Passes | Code |
|---|---|---|
| same_as | the other field exists and `===` | crossfield.same_as |
| different_from | the other field is absent or `!==` | crossfield.different_from |
| required_with | the other field is not filled in, or this one is | crossfield.required_with |
| required_without | the other field is filled in, or this one is | crossfield.required_without |

## 9. Financial

| Alias | Passes | Code |
|---|---|---|
| luhn | a string or int; spaces and hyphens removed; ≥ 8 digits; Luhn sum divisible by 10 | financial.luhn |
| iban | whitespace removed, case ignored; country letters, two check digits, 11–30 alphanumerics; ISO 7064 MOD 97-10 remainder 1; national formats not checked | financial.iban |
| bic | trimmed, case ignored; 4 letters, 2 letters, 2 alphanumerics, optional 3 alphanumerics | financial.bic |

## 10. Brazilian

| Alias | Passes | Code |
|---|---|---|
| cpf | 11 digits, bare or `000.000.000-00`; modulo 11 check digits; not one repeated digit | brazilian.cpf |
| cnpj | 12 characters `[0-9A-Z]` and 2 digits, bare or `AA.AAA.AAA/AAAA-00`; each character counts ASCII − 48 in the modulo 11 check; not 14 identical characters; lower case fails (ADR-008) | brazilian.cnpj |
| cep | 8 digits, bare or `00000-000`; not all zeros | brazilian.cep |
| pis | 11 digits, bare or `000.00000.00-0`; modulo 11 with weights 3, 2, 9 … 2; not one repeated digit | brazilian.pis |
