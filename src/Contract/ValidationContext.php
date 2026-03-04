<?php

declare(strict_types=1);

namespace KaririCode\Validator\Contract;

/**
 * Immutable context passed to every validation rule during execution.
 *
 * Carries metadata about the validation environment: the field being
 * validated, the root data structure, rule-specific configuration, and
 * the validation group. Rules use context to implement cross-field
 * validation (e.g. "confirm password matches password") without
 * coupling to the outer validation engine.
 *
 * ## ARFA 1.3 Alignment
 *
 * P1 (Immutable State): Context is readonly — rules cannot mutate it.
 * P3 (Adaptive Context): Carries runtime metadata for rule adaptation.
 *
 * @package KaririCode\Validator\Contract
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
interface ValidationContext
{
    /**
     * Field name being validated (dot-notation for nested: `address.city`).
     */
    public function getFieldName(): string;

    /**
     * Root data structure under validation.
     *
     * Enables cross-field validation rules like `SameAs`, `RequiredWith`.
     *
     * @return array<string, mixed>
     */
    public function getRootData(): array;

    /**
     * Rule-specific configuration parameters.
     *
     * @return array<string, mixed> e.g. ['min' => 3, 'max' => 255]
     */
    public function getParameters(): array;

    /**
     * Single parameter by key with optional default.
     */
    public function getParameter(string $key, mixed $default = null): mixed;

    /**
     * Active validation group (null = default group).
     *
     * Groups allow selective validation: `create`, `update`, `import`.
     */
    public function getGroup(): ?string;

    /**
     * New context scoped to a different field.
     */
    public function withField(string $field): static;

    /**
     * New context with merged parameters.
     *
     * @param array<string, mixed> $parameters
     */
    public function withParameters(array $parameters): static;
}
