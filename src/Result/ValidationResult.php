<?php

declare(strict_types=1);

namespace KaririCode\Validator\Result;

// ── Result: ValidationResult ─────────────────────────────────────────
// Every error a validation found, in the order the rules ran.
// ─────────────────────────────────────────────────────────────────────

/**
 * Collects the errors of one validation, field by field and rule by rule,
 * and answers the questions callers ask of them: is the data valid, what
 * failed on this field, which fields failed, what are the messages per
 * field. Results combine without changing either side.
 *
 * Preconditions: none.
 *
 * Thread-safety: immutable; merge() and with() return new results.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     if (!$result->isValid()) {
 *         return new JsonResponse(['errors' => $result->messages()], 422);
 *     }
 *
 * References: ADR-002 (collect-all); SPEC-001 §6.
 */
final readonly class ValidationResult
{
    /** @var list<ValidationError> */
    public array $errors;

    public function __construct(ValidationError ...$errors)
    {
        $this->errors = array_values($errors);
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function errorCount(): int
    {
        return count($this->errors);
    }

    /**
     * @return list<ValidationError>
     */
    public function errorsFor(string $field): array
    {
        return array_values(array_filter($this->errors, static fn(ValidationError $error): bool => $error->field === $field));
    }

    public function firstErrorFor(string $field): ?ValidationError
    {
        return $this->errorsFor($field)[0] ?? null;
    }

    /**
     * @return list<string>
     */
    public function failedFields(): array
    {
        return array_keys($this->messages());
    }

    /**
     * @return array<string, list<string>> field => messages, fields in the order they failed
     */
    public function messages(): array
    {
        $messages = [];

        foreach ($this->errors as $error) {
            $messages[$error->field][] = $error->message;
        }

        return $messages;
    }

    public function merge(self $other): self
    {
        return new self(...$this->errors, ...$other->errors);
    }

    public function with(ValidationError $error): self
    {
        return new self(...$this->errors, ...[$error]);
    }
}
