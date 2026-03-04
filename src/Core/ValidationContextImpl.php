<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\Validator\Contract\ValidationContext as ValidationContextContract;

/**
 * Immutable validation context carrying field metadata and root data.
 *
 * Follows ARFA 1.3 P1 (Immutable State Transformation): once created,
 * context state cannot be mutated. New contexts are derived via
 * `withField()` and `withParameters()` for nested validation.
 *
 * ## Structural Sharing
 *
 * Derived contexts share the root data reference (copy-on-read),
 * avoiding O(n) deep copies on every field transition.
 *
 * @package KaririCode\Validator\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ValidationContextImpl implements ValidationContextContract
{
    /**
     * @param string               $fieldName  Current field (dot-notation)
     * @param array<string, mixed> $rootData   Root data structure
     * @param array<string, mixed> $parameters Rule-specific config
     * @param string|null          $group      Active validation group
     */
    public function __construct(
        private string $fieldName,
        private array $rootData = [],
        private array $parameters = [],
        private ?string $group = null,
    ) {
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getRootData(): array
    {
        return $this->rootData;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * Derives a new context for a different field.
     */
    public function withField(string $fieldName): static
    {
        return new self($fieldName, $this->rootData, $this->parameters, $this->group);
    }

    /**
     * Derives a new context with additional parameters (merged).
     *
     * @param array<string, mixed> $parameters
     */
    public function withParameters(array $parameters): static
    {
        return new self($this->fieldName, $this->rootData, [...$this->parameters, ...$parameters], $this->group);
    }

    /**
     * Factory for initial context creation.
     *
     * @param array<string, mixed> $rootData Root data to validate
     * @param string|null          $group    Validation group
     */
    public static function create(array $rootData, ?string $group = null): self
    {
        return new self('', $rootData, [], $group);
    }
}
