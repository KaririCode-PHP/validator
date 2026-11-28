<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

// ── Core: DataPath ───────────────────────────────────────────────────
// Reads a field out of nested data by its dotted path.
// ─────────────────────────────────────────────────────────────────────

/**
 * Finds the value a field path names: "email" is a key, "address.city"
 * walks into nested arrays. A key that contains dots itself is matched
 * first, so flat form data with dotted names works too. A missing field
 * and a field set to null read the same (null), but exists() tells them
 * apart.
 *
 * Preconditions: none; numeric segments match integer keys ("items.0").
 *
 * Thread-safety: stateless.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     DataPath::value(['address' => ['city' => 'Crato']], 'address.city'); // 'Crato'
 *
 * References: SPEC-001 §4 (INV-005).
 */
final class DataPath
{
    /**
     * @param array<array-key, mixed> $data
     */
    public static function exists(array $data, string $path): bool
    {
        return self::walk($data, $path)[0];
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function value(array $data, string $path): mixed
    {
        return self::walk($data, $path)[1];
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return array{bool, mixed}
     */
    private static function walk(array $data, string $path): array
    {
        if (array_key_exists($path, $data)) {
            return [true, $data[$path]];
        }

        $current = $data;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return [false, null];
            }

            /** @var mixed $current */
            $current = $current[$segment];
        }

        return [true, $current];
    }
}
