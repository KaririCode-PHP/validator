<?php

declare(strict_types=1);

namespace KaririCode\Validator\Configuration;

use KaririCode\Validator\Exception\ConfigurationException;

// ── Configuration: ValidatorConfiguration ────────────────────────────
// How the engine collects errors and in which language it reports them.
// ─────────────────────────────────────────────────────────────────────

/**
 * Sets the locale messages are resolved in, whether a field stops at its
 * first error (failFast), how many errors a field may collect (0: no
 * limit) and extra messages per locale, which replace or add to the
 * built-in ones.
 *
 * Preconditions: the locale is non-empty; the limit is 0 or more; custom
 * messages map locales to maps of error codes to strings.
 *
 * Thread-safety: immutable.
 *
 * Side effects: none; invalid settings throw ConfigurationException.
 *
 * Example:
 *
 *     new ValidatorConfiguration(defaultLocale: 'pt_BR', maxErrorsPerField: 1, customMessages: ['pt_BR' => ['custom.even' => 'O campo {field} deve ser par.']]);
 *
 * References: SPEC-001 §8 (INV-006, INV-007).
 */
final readonly class ValidatorConfiguration
{
    /** @var array<string, array<string, string>> */
    public array $customMessages;

    /**
     * @param array<array-key, mixed> $customMessages locale => error code => message
     */
    public function __construct(
        public string $defaultLocale = 'en',
        public bool $failFast = false,
        public int $maxErrorsPerField = 0,
        array $customMessages = [],
    ) {
        if ($defaultLocale === '') {
            throw ConfigurationException::invalidSetting(setting: 'defaultLocale', reason: 'must not be empty');
        }

        if ($maxErrorsPerField < 0) {
            throw ConfigurationException::invalidSetting(setting: 'maxErrorsPerField', reason: 'must be 0 (no limit) or more');
        }

        $this->customMessages = self::catalog($customMessages);
    }

    /**
     * @param array<array-key, mixed> $messages
     *
     * @return array<string, array<string, string>>
     */
    private static function catalog(array $messages): array
    {
        foreach ($messages as $locale => $catalog) {
            if (!is_string($locale) || !is_array($catalog) || array_any($catalog, static fn(mixed $message, int|string $code): bool => !is_string($code) || !is_string($message))) {
                throw ConfigurationException::invalidSetting(setting: 'customMessages', reason: 'must map locales to maps of error codes to strings');
            }
        }

        /** @var array<string, array<string, string>> $messages */
        return $messages;
    }
}
