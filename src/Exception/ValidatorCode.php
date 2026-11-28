<?php

declare(strict_types=1);

namespace KaririCode\Validator\Exception;

use KaririCode\Exception\CodeBlock;
use KaririCode\Exception\ErrorCode;

// ── Exception: ValidatorCode ─────────────────────────────────────────
// Every code this package throws, inside block VAL 3200–3299.
// ─────────────────────────────────────────────────────────────────────

/**
 * Names each failure the package can throw, inside the block
 * kariricode/exception's CODE-BLOCKS.md records for kariricode/validator:
 * 3200–3219 rule definitions and the registry, 3220–3229 settings,
 * 3230–3239 the outcome of a validation, 3240–3249 the integrations.
 *
 * ARFA 1.3 E-003: backed because getCode() reads the integer through
 * kariricode/exception's ErrorCode; every other enum of the package is
 * pure.
 *
 * Preconditions: none; the cases are constants.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $error->errorCode === ValidatorCode::UnknownRuleAlias; // [VAL_3202]
 *
 * References: ADR-004; kariricode/exception CODE-BLOCKS.md.
 */
enum ValidatorCode: int implements ErrorCode
{
    case DuplicateRuleAlias = 3201;
    case UnknownRuleAlias = 3202;
    case InvalidRuleDefinition = 3203;
    case MissingRuleParameter = 3204;
    case InvalidRuleParameter = 3205;
    case NamedAttributeArguments = 3206;
    case InvalidSetting = 3220;
    case InvalidBinding = 3221;
    case ValidationFailed = 3230;
    case UnsupportedInput = 3240;

    #[\Override]
    public static function prefix(): string
    {
        return 'VAL';
    }

    #[\Override]
    public static function block(): CodeBlock
    {
        return new CodeBlock(3200, 3299);
    }
}
