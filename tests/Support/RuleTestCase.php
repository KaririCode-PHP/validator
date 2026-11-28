<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Support;

use KaririCode\Validator\Contract\ValidationRule;
use KaririCode\Validator\Core\MessageCatalog;
use KaririCode\Validator\Core\RuleContext;
use KaririCode\Validator\Exception\InvalidRuleException;
use KaririCode\Validator\Exception\ValidatorCode;
use PHPUnit\Framework\TestCase;

// ── Support: RuleTestCase ────────────────────────────────────────────
// Shared checks of the rule suites: accept, reject, report, refuse.
// ─────────────────────────────────────────────────────────────────────

/** Base of the rule suites: a context for field "field" and three assertions. */
abstract class RuleTestCase extends TestCase
{
    /**
     * @param array<string, mixed>    $parameters
     * @param array<array-key, mixed> $data
     */
    protected static function context(array $parameters = [], array $data = []): RuleContext
    {
        return new RuleContext(data: $data, field: 'field', parameters: $parameters);
    }

    /**
     * @param array<string, mixed>    $parameters
     * @param array<array-key, mixed> $data
     */
    protected static function assertRule(bool $expected, ValidationRule $rule, mixed $value, array $parameters = [], array $data = []): void
    {
        self::assertSame($expected, $rule->validate($value, self::context($parameters, $data)), get_debug_type($value) . ' ' . var_export($value, true));
    }

    /**
     * The violation names its code, and its fallback is the English
     * message of the catalog.
     *
     * @param array<string, mixed> $parameters
     */
    protected static function assertViolation(string $code, ValidationRule $rule, array $parameters = []): void
    {
        $violation = $rule->violation(self::context($parameters));

        self::assertSame($code, $violation->code);
        self::assertSame(MessageCatalog::MESSAGES['en'][$code], $violation->message);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected static function assertRefused(ValidatorCode $code, ValidationRule $rule, array $parameters, mixed $value = 'value'): void
    {
        try {
            $rule->validate($value, self::context($parameters));
        } catch (InvalidRuleException $exception) {
            self::assertSame($code, $exception->errorCode);
            self::assertStringContainsString('"field"', $exception->getMessage());

            return;
        }

        self::fail('The rule accepted a misconfiguration.');
    }
}
