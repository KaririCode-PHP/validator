<?php

declare(strict_types=1);

namespace KaririCode\Validator\Configuration;

/**
 * Configuration holder for the validator engine.
 *
 * Encapsulates configurable options: default locale, fail-fast mode,
 * max errors per field, and custom message overrides.
 *
 * @package KaririCode\Validator\Configuration
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ValidatorConfiguration
{
    /**
     * @param string                        $defaultLocale  Default locale for messages
     * @param bool                          $failFast       Stop on first error per field
     * @param int                           $maxErrorsPerField Max errors collected per field (0 = unlimited)
     * @param array<string, array<string>>  $customMessages Error code => [locale => message]
     */
    public function __construct(
        public string $defaultLocale = 'en',
        public bool $failFast = false,
        public int $maxErrorsPerField = 0,
        public array $customMessages = [],
    ) {
    }
}
