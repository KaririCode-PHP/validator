<?php
declare(strict_types=1);
namespace KaririCode\Validator\Rule\Logic;
use KaririCode\Validator\Contract\{ValidationContext, ValidationRule};

/**
 * OR-composes multiple rules: at least one must pass.
 *
 * @package KaririCode\Validator\Rule\Logic
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class AtLeastOneOfRule implements ValidationRule
{
    /** @var array<ValidationRule> */
    private array $rules;

    public function __construct(ValidationRule ...$rules)
    {
        $this->rules = $rules;
    }

    public function validate(mixed $value, ValidationContext $context): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->validate($value, $context)) {
                return true;
            }
        }
        return false;
    }

    public function getErrorCode(): string { return 'logic.at_least_one_of'; }
    public function getDefaultMessage(): string { return 'The {field} must pass at least one validation rule.'; }
}
