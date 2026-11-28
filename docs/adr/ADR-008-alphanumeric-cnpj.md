# ADR-008: The Alphanumeric CNPJ, All 26 Letters, Upper Case

## Status

Accepted — V3.0.0

## Date

2026-11-28

## Context

Instrução Normativa RFB nº 2.229/2024 makes the CNPJ alphanumeric: the
twelve characters of root and order may be digits or letters, the two
check digits stay numeric, and the Receita Federal issues such numbers
from July 2026. Every CNPJ validator must accept them.

- The September 2026 tarball accepted numeric CNPJs only, and stripped
  every non-digit first, so "abc11.222.333/0001-81xyz" passed.
- The March 2026 main line accepted letters, but excluded I, O and Q
  "per Nota Técnica Conjunta RFB nº 2025.001".

The RFB's own questions and answers on the alphanumeric CNPJ say the
characters may be "quaisquer uma das 26 letras de A até Z" (question
1), that the twelve positions may hold digits 0–9 and upper-case
letters A–Z (question 2), and that some letter combinations will be
withheld to avoid offensive words or confusion with numbers, a control
the RFB applies internally. NT Conjunta 2025.001 for the electronic tax
documents gives the field pattern `[A-Z0-9]{12}[0-9]{2}`. A source that
lists letters "not permitted yet" describes issuance, not the format.

### Prior Art

- RFB, Perguntas e Respostas — CNPJ alfanumérico (questions 1, 2, 14,
  21 and 23); the worked example 12.ABC.345/01DE-35.
- NT Conjunta DF-e 2025.001 (CNPJ field pattern).

## Decision

1. **Format.** Twelve characters `[0-9A-Z]` and two digits, bare or
   formatted as `AA.AAA.AAA/AAAA-00`, each separator optional. Lower-case
   letters fail: the RFB format defines upper case; the sanitizer
   normalizes case before validation if an application wants that.
2. **Letters.** All 26 letters are accepted. Which combinations the RFB
   issues is not a validation rule; rejecting I, O and Q would reject
   numbers the format allows.
3. **Check digits.** Each character counts `ord(character) − 48` (0–9
   stay 0–9, A=17 … Z=42), weights 5…2,9…2 and 6…2,9…2, modulo 11, a
   remainder below 2 giving 0. For numeric CNPJs this is the old
   algorithm.
4. **Rejections.** Fourteen identical characters fail (00000000000000
   passes the arithmetic). Text around the number, a letter in a check
   digit and lower-case letters fail.
5. The same strictness applies to `cpf`, `cep` and `pis`: bare or
   formatted digits, nothing else.

## Consequences

### Positive

- New CNPJs validate, including the RFB's example; numeric CNPJs behave
  as before; garbage around a number no longer passes.

### Negative

- **Breaking:** input with stray characters, or lower-case letters, now
  fails; sanitize first.
- If the RFB ever restricts the format itself, the pattern changes in a
  minor release with that publication as its reference.

## Alternatives Considered

- **Exclude I, O and Q,** as the main line did: not in the RFB's format
  or in the DF-e pattern.
- **Accept lower case and upper-case it:** lenient, and a validator that
  changes what it validates hides what will be stored.
- **Strip any non-alphanumeric character first:** the tarball's
  behaviour for CPF, which let arbitrary text through.

## References

- Instrução Normativa RFB nº 2.229, de 15 de outubro de 2024.
- Receita Federal, Perguntas e Respostas sobre o CNPJ alfanumérico,
  https://www.gov.br/receitafederal/pt-br/centrais-de-conteudo/publicacoes/perguntas-e-respostas/cnpj/cnpj-alfanumerico.pdf
- NT Conjunta DF-e 2025.001.
- SPEC-002 §10.
