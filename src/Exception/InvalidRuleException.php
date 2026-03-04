<?php

declare(strict_types=1);

namespace KaririCode\Validator\Exception;

/**
 * Thrown for rule configuration errors (programmer mistakes).
 *
 * @package KaririCode\Validator\Exception
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class InvalidRuleException extends \InvalidArgumentException
{
    public static function duplicateAlias(string $alias): self
    {
        return new self("Rule alias '{$alias}' is already registered.");
    }

    public static function unknownAlias(string $alias): self
    {
        return new self("No validation rule registered for alias '{$alias}'.");
    }

    public static function invalidConfiguration(string $rule, string $reason): self
    {
        return new self("Invalid configuration for rule '{$rule}': {$reason}");
    }
}
