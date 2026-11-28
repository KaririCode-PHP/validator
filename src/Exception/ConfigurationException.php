<?php

declare(strict_types=1);

namespace KaririCode\Validator\Exception;

use KaririCode\Exception\ConfigurationException as ConfigurationBase;

// ── Exception: ConfigurationException ────────────────────────────────
// A validator setting or container binding is unusable (codes 3220–3221).
// ─────────────────────────────────────────────────────────────────────

/**
 * Reports a ValidatorConfiguration value the engine cannot work with (an
 * empty locale, a negative error limit, a message catalog that is not a
 * map of locales to maps of error codes to strings), or a container that
 * resolves one of the provider's services to the wrong type.
 *
 * Preconditions: built only through the named constructor.
 *
 * Thread-safety: an exception, created per failure.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     throw ConfigurationException::invalidSetting('maxErrorsPerField', 'must be 0 (no limit) or more');
 *
 * References: ADR-004.
 */
final class ConfigurationException extends ConfigurationBase implements ValidatorException
{
    public static function invalidSetting(string $setting, string $reason): self
    {
        return new self(ValidatorCode::InvalidSetting, 'The validator setting ' . self::quote($setting) . " {$reason}.");
    }

    public static function invalidBinding(string $id, string $type): self
    {
        return new self(ValidatorCode::InvalidBinding, 'The container resolves ' . self::quote($id) . " to {$type}, which is not an instance of it.");
    }
}
