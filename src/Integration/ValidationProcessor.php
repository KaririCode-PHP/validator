<?php

declare(strict_types=1);

namespace KaririCode\Validator\Integration;

use KaririCode\Contract\Processor\Processor;
use KaririCode\Contract\Validator\Validator;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Exception\UnsupportedInputException;
use KaririCode\Validator\Exception\ValidationException;
use KaririCode\Validator\Result\ValidationResult;

// ── Integration: ValidationProcessor ─────────────────────────────────
// A validation step for kariricode/processor-pipeline and other contract consumers.
// ─────────────────────────────────────────────────────────────────────

/**
 * Validates an array of fields against a fixed rule map behind the
 * kariricode/contract ports: as a Processor it passes valid input through
 * unchanged and throws ValidationException with the result otherwise, so
 * a pipeline stops at invalid input; as a Validator it returns the
 * ValidationResult.
 *
 * Preconditions: the input is an array of fields; anything else throws
 * UnsupportedInputException.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none beyond the engine's observer.
 *
 * Example:
 *
 *     $pipeline->addProcessor(new ValidationProcessor($engine, ['email' => ['required', 'email']]));
 *
 * References: kariricode/contract Processor and Validator; ADR-002.
 */
final readonly class ValidationProcessor implements Processor, Validator
{
    /**
     * @param array<array-key, mixed> $fieldRules field path => list of rule definitions
     */
    public function __construct(
        private ValidatorEngine $engine,
        private array $fieldRules,
        private ?string $group = null,
    ) {}

    #[\Override]
    public function process(mixed $input): mixed
    {
        $result = $this->validate($input);

        return $result->isValid() ? $input : throw ValidationException::failed($result);
    }

    #[\Override]
    public function validate(mixed $input): ValidationResult
    {
        if (!is_array($input)) {
            throw UnsupportedInputException::notAnArray(get_debug_type($input));
        }

        return $this->engine->validate(data: $input, fieldRules: $this->fieldRules, group: $this->group);
    }
}
