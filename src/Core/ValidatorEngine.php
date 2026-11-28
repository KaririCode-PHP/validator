<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use Closure;
use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Contract\MessageResolver;
use KaririCode\Validator\Contract\RuleRegistry;
use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Event\ValidationCompletedEvent;
use KaririCode\Validator\Event\ValidationStartedEvent;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Result\ValidationError;
use KaririCode\Validator\Result\ValidationResult;
use KaririCode\Validator\Result\Violation;
use KaririCode\Validator\Rule\Logic\NullableRule;

// ── Core: ValidatorEngine ────────────────────────────────────────────
// Runs the rules of each field and collects every failure.
// ─────────────────────────────────────────────────────────────────────

/**
 * Validates an array of fields against a rule map: for each field, it
 * reads the value by its dotted path, applies the field's rules in order
 * with their parameters, and records a ValidationError with a resolved
 * message for every rule that fails. By default every rule runs
 * (collect-all); failFast stops a field at its first error and
 * maxErrorsPerField caps the errors per field. A field whose rules
 * include 'nullable' and whose value is null passes without running the
 * others. The observer receives a ValidationStartedEvent and a
 * ValidationCompletedEvent per call.
 *
 * Preconditions: a rule definition is an alias, a ValidationRule, or a
 * list [alias or rule, parameters]; anything else is an
 * InvalidRuleException, as is an unknown alias or a missing parameter.
 *
 * Thread-safety: immutable; the registry is read, never written.
 *
 * Side effects: the observer is called twice per validation.
 *
 * Example:
 *
 *     $result = (new ValidatorEngine())->validate(
 *         ['email' => 'ana@example.com', 'age' => 17],
 *         ['email' => ['required', 'email'], 'age' => ['integer', ['range', ['min' => 18]]]],
 *     );
 *     $result->messages(); // ['age' => ['The age must be at least 18.']]
 *
 * References: ADR-001; ADR-002; ADR-006; SPEC-001.
 */
final readonly class ValidatorEngine
{
    public RuleRegistry $registry;

    public ValidatorConfiguration $configuration;

    public MessageResolver $messages;

    private ?Closure $observer;

    public function __construct(
        ?RuleRegistry $registry = null,
        ?ValidatorConfiguration $configuration = null,
        ?MessageResolver $messages = null,
        ?Closure $observer = null,
    ) {
        $this->configuration = $configuration ?? new ValidatorConfiguration();
        $this->registry = $registry ?? InMemoryRuleRegistry::withDefaults();
        $this->messages = $messages ?? DefaultMessageResolver::fromConfiguration($this->configuration);
        $this->observer = $observer;
    }

    /**
     * @param array<array-key, mixed> $data       the fields to validate
     * @param array<array-key, mixed> $fieldRules field path => list of rule definitions
     */
    public function validate(array $data, array $fieldRules, ?string $group = null): ValidationResult
    {
        $startedAt = hrtime(true);
        $fields = array_map(strval(...), array_keys($fieldRules));
        $this->emit(new ValidationStartedEvent(fields: $fields, group: $group, timestamp: microtime(true)));
        $context = new RuleContext(data: $data, group: $group);
        $errors = [];

        /** @var mixed $rules */
        foreach ($fieldRules as $field => $rules) {
            array_push($errors, ...$this->validateField($context->withField((string) $field), $rules));
        }

        $result = new ValidationResult(...$errors);
        $this->emit(new ValidationCompletedEvent(result: $result, durationMs: (hrtime(true) - $startedAt) / 1_000_000));

        return $result;
    }

    /**
     * @return list<ValidationError>
     */
    private function validateField(RuleContext $context, mixed $rules): array
    {
        $definitions = $this->definitions($context->field, $rules);
        /** @var mixed $value */
        $value = $context->fieldValue($context->field);

        if ($value === null && array_any($definitions, static fn(array $definition): bool => $definition[0] instanceof NullableRule)) {
            return [];
        }

        $errors = [];

        foreach ($definitions as [$rule, $parameters]) {
            $ruleContext = $context->withParameters($parameters);

            if ($rule->validate($value, $ruleContext)) {
                continue;
            }

            $errors[] = $this->error($rule->violation($ruleContext), $ruleContext);

            if ($this->fieldIsDone(count($errors))) {
                break;
            }
        }

        return $errors;
    }

    /**
     * @return list<array{ValidationRule, array<string, mixed>}>
     */
    private function definitions(string $field, mixed $rules): array
    {
        if (!is_array($rules) || !array_is_list($rules)) {
            throw InvalidRuleException::invalidDefinition(field: $field, reason: 'the rules of a field are a list');
        }

        return array_map(fn(mixed $definition): array => $this->definition($field, $definition), $rules);
    }

    /**
     * @return array{ValidationRule, array<string, mixed>}
     */
    private function definition(string $field, mixed $definition): array
    {
        if ($definition instanceof ValidationRule) {
            return [$definition, []];
        }

        if (is_string($definition)) {
            return [$this->registry->resolve($definition), []];
        }

        if (!is_array($definition) || !array_is_list($definition) || count($definition) > 2) {
            throw InvalidRuleException::invalidDefinition(field: $field, reason: 'a rule is an alias, a ValidationRule, or [alias or rule, parameters]');
        }

        return [$this->rule($field, $definition[0] ?? null), $this->parameters($field, $definition[1] ?? [])];
    }

    private function rule(string $field, mixed $rule): ValidationRule
    {
        if (is_string($rule)) {
            return $this->registry->resolve($rule);
        }

        return $rule instanceof ValidationRule ? $rule : throw InvalidRuleException::invalidDefinition(field: $field, reason: 'the first item names the rule, by alias or as a ValidationRule');
    }

    /**
     * @return array<string, mixed>
     */
    private function parameters(string $field, mixed $parameters): array
    {
        if (!is_array($parameters) || array_filter(array_keys($parameters), is_int(...)) !== []) {
            throw InvalidRuleException::invalidDefinition(field: $field, reason: 'the parameters of a rule are a map of names to values');
        }

        /** @var array<string, mixed> $parameters */
        return $parameters;
    }

    private function fieldIsDone(int $errors): bool
    {
        $limit = $this->configuration->maxErrorsPerField;

        return $this->configuration->failFast || ($limit > 0 && $errors >= $limit);
    }

    private function error(Violation $violation, RuleContext $context): ValidationError
    {
        $parameters = [...$context->parameters, ...$violation->parameters];

        return new ValidationError(
            field: $context->field,
            errorCode: $violation->code,
            message: $this->messages->resolve(errorCode: $violation->code, fallback: $violation->message, parameters: [...$parameters, 'field' => $context->field]),
            parameters: $parameters,
        );
    }

    private function emit(ValidationStartedEvent|ValidationCompletedEvent $event): void
    {
        if ($this->observer !== null) {
            ($this->observer)($event);
        }
    }
}
