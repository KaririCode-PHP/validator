<?php

declare(strict_types=1);

namespace KaririCode\Validator\Integration;

use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Exception\ValidationException;
use KaririCode\Validator\Result\ValidationResult;

/**
 * Bridge between KaririCode\Validator and KaririCode\ProcessorPipeline.
 *
 * Wraps the ValidatorEngine as a Processor, enabling validation as a
 * pipeline step in ARFA 1.3 reactive flows.
 *
 * ```php
 * $pipeline = new ProcessorPipeline([
 *     new NormalizationProcessor(),
 *     new ProcessorBridge($engine, $rules),
 *     new PersistenceProcessor(),
 * ]);
 * ```
 *
 * @package KaririCode\Validator\Integration
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ProcessorBridge
{
    /**
     * @param ValidatorEngine                                       $engine    Validator engine
     * @param array<string, array<string|array>>                    $fieldRules Field-rule map
     * @param bool                                                  $throwOnFailure Throw ValidationException on failure
     */
    public function __construct(
        private ValidatorEngine $engine,
        private array $fieldRules,
        private bool $throwOnFailure = true,
    ) {
    }

    /**
     * Validates the input data and returns it unchanged if valid.
     *
     * @param array<string, mixed> $data Input data
     *
     * @return array{data: array<string, mixed>, result: ValidationResult}
     *
     * @throws ValidationException If validation fails and throwOnFailure is true
     */
    public function process(array $data): array
    {
        $result = $this->engine->validate($data, $this->fieldRules);

        if (!$result->isValid() && $this->throwOnFailure) {
            throw ValidationException::fromResult($result);
        }

        return ['data' => $data, 'result' => $result];
    }
}
