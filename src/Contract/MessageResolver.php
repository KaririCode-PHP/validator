<?php

declare(strict_types=1);

namespace KaririCode\Validator\Contract;

/**
 * Resolves validation error messages with i18n and placeholder support.
 *
 * Separates message resolution from validation logic, enabling:
 * locale-based messages, custom message overrides per field,
 * and placeholder interpolation (`{min}`, `{max}`, `{field}`).
 *
 * ## ARFA 1.3 Alignment
 *
 * P4 (Protocol Agnostic): Messages resolved independently of transport.
 *
 * @package KaririCode\Validator\Contract
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
interface MessageResolver
{
    /**
     * Resolves the error message for a given error code.
     *
     * @param string               $errorCode  Dot-notation code (e.g. 'string.email')
     * @param array<string, mixed> $parameters Placeholder values for interpolation
     * @param string|null          $locale     Target locale (null = default)
     *
     * @return string Resolved, interpolated message
     */
    public function resolve(
        string $errorCode,
        array $parameters = [],
        ?string $locale = null,
    ): string;
}
