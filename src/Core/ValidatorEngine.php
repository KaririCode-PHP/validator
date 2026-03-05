<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Contract\MessageResolver;
use KaririCode\Validator\Contract\RuleRegistry;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Result\ValidationResult;

/**
 * Central validation engine orchestrating rules against data.
 *
 * Accepts a field-rule map and validates each field against its
 * configured rules, collecting errors into a ValidationResult.
 *
 * Supports fail-fast mode (stop on first error per field),
 * max-errors-per-field limiting, and group-based filtering.
 *
 * ## ARFA 1.3 Alignment
 *
 * P1 (Immutable State): Input data is not mutated.
 * P2 (Reactive Composition): Rules compose via pipeline per field.
 * P5 (Observability): Events emitted for start/complete/fail.
 *
 * References:
 * - Martin, R.C. "Clean Architecture", Ch. 22 (Use Cases)
 * - Evans, E. "Domain-Driven Design", Ch. 9 (Specification Pattern)
 *
 * @package KaririCode\Validator\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ValidatorEngine
{
    public function __construct(private RuleRegistry $registry, private ?MessageResolver $messageResolver = null, private ?ValidatorConfiguration $configuration = new ValidatorConfiguration())
    {
    }

    /**
     * Validates data against a field-rule definition map.
     *
     * @param array<string, mixed>       $data       Data to validate
     * @param array<string, list<mixed>> $fieldRules Map of field => rules
     * @param string|null                $group      Validation group
     *
     * Rule format per field:
     * - string: alias name (e.g. 'required', 'email')
     * - ValidationRule instance: direct rule
     * - array: ['alias', ['param' => 'value']] — alias with parameters
     */
    public function validate(
        array $data,
        array $fieldRules,
        ?string $group = null,
    ): ValidationResult {
        $result = new ValidationResult();
        $baseContext = ValidationContextImpl::create($data, $group);

        foreach ($fieldRules as $field => $rules) {
            $value = $this->resolveValue($data, $field);
            $fieldContext = $baseContext->withField($field);
            $fieldErrorCount = 0;

            foreach ($rules as $ruleDefinition) {
                if ($this->shouldStopField($fieldErrorCount)) {
                    break;
                }

                if (! \is_string($ruleDefinition) && ! $ruleDefinition instanceof ValidationRule && ! \is_array($ruleDefinition)) {
                    continue;
                }

                $hasError = $this->applyRule($ruleDefinition, $value, $fieldContext, $field, $result);

                if ($hasError) {
                    ++$fieldErrorCount;

                    \assert($this->configuration instanceof ValidatorConfiguration);
                    if ($this->configuration->failFast) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns the current configuration.
     */
    public function getConfiguration(): ValidatorConfiguration|null
    {
        return $this->configuration;
    }

    /**
     * Resolves a value from nested data using dot-notation.
     *
     * @param array<string, mixed> $data
     */
    private function resolveValue(array $data, string $field): mixed
    {
        $keys = explode('.', $field);
        $current = $data;

        foreach ($keys as $key) {
            if (! \is_array($current) || ! \array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Applies a single rule definition to a value. Returns true if error added.
     *
     * @param string|ValidationRule|array<mixed> $ruleDefinition
     */
    private function applyRule(
        string|ValidationRule|array $ruleDefinition,
        mixed $value,
        ValidationContextImpl $context,
        string $field,
        ValidationResult $result,
    ): bool {
        [$rule, $parameters] = $this->resolveRuleDefinition($ruleDefinition);
        $parameterizedContext = $parameters !== [] ? $context->withParameters($parameters) : $context;

        if (! $rule->validate($value, $parameterizedContext)) {
            $message = $this->resolveMessage($rule, $parameters, $field);
            $result->add($field, $rule->getErrorCode(), $message, $parameters);

            return true;
        }

        return false;
    }

    /**
     * Resolves a rule definition into a ValidationRule + parameters.
     *
     * @param string|ValidationRule|array<mixed> $definition
     * @return array{0: ValidationRule, 1: array<string, mixed>}
     */
    private function resolveRuleDefinition(string|ValidationRule|array $definition): array
    {
        if ($definition instanceof ValidationRule) {
            return [$definition, []];
        }

        if (\is_string($definition)) {
            return [$this->registry->resolve($definition), []];
        }

        // Array format: ['alias', ['min' => 3, 'max' => 255]]
        $alias = $definition[0];
        $params = isset($definition[1]) && \is_array($definition[1]) ? $definition[1] : [];

        $rule = \is_string($alias)
            ? $this->registry->resolve($alias)
            : $alias;

        \assert($rule instanceof ValidationRule);

        return [$rule, $params];
    }

    /**
     * Determines whether to stop collecting errors for the current field.
     */
    private function shouldStopField(int $fieldErrorCount): bool
    {
        \assert($this->configuration instanceof ValidatorConfiguration);
        $max = $this->configuration->maxErrorsPerField;

        return $max > 0 && $fieldErrorCount >= $max;
    }

    /**
     * Resolves the error message using MessageResolver or rule default.
     *
     * @param array<string, mixed> $parameters
     */
    private function resolveMessage(
        ValidationRule $rule,
        array $parameters,
        string $field,
    ): string {
        if ($this->messageResolver instanceof MessageResolver) {
            return $this->messageResolver->resolve(
                $rule->getErrorCode(),
                array_merge($parameters, ['field' => $field]),
            );
        }

        return $this->interpolate($rule->getDefaultMessage(), array_merge($parameters, ['field' => $field]));
    }

    /**
     * Simple placeholder interpolation: `{key}` → value.
     *
     * @param array<string, mixed> $parameters
     */
    private function interpolate(string $message, array $parameters): string
    {
        $replacements = [];
        foreach ($parameters as $key => $value) {
            if (\is_scalar($value) || $value === null) {
                $replacements['{' . $key . '}'] = (string) ($value ?? '');
            }
        }

        return strtr($message, $replacements);
    }
}
