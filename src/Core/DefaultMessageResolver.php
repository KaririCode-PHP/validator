<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use DateTimeInterface;
use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Contract\MessageResolver;

// ── Core: DefaultMessageResolver ─────────────────────────────────────
// Messages from a catalog per locale, with English and the rule as fallbacks.
// ─────────────────────────────────────────────────────────────────────

/**
 * Resolves an error code to the message of the requested locale, then to
 * the English one, then to the fallback the rule reported, and fills the
 * {placeholders}: strings and numbers as written, booleans as true and
 * false, lists of scalars joined with commas, dates as Y-m-d (with the
 * time when it is not midnight). Values of other types leave their
 * placeholder untouched.
 *
 * Preconditions: messages map locales to maps of error codes to strings.
 *
 * Thread-safety: immutable; withMessages() returns a copy.
 *
 * Side effects: none.
 *
 * Example:
 *
 *     $resolver = (new DefaultMessageResolver('pt_BR'))->withMessages('pt_BR', ['custom.even' => 'O campo {field} deve ser par.']);
 *
 * References: ADR-005.
 */
final readonly class DefaultMessageResolver implements MessageResolver
{
    /**
     * @param array<string, array<string, string>> $messages locale => error code => message
     */
    public function __construct(
        public string $locale = 'en',
        public array $messages = MessageCatalog::MESSAGES,
    ) {}

    public static function fromConfiguration(ValidatorConfiguration $configuration): self
    {
        $resolver = new self($configuration->defaultLocale);

        foreach ($configuration->customMessages as $locale => $messages) {
            $resolver = $resolver->withMessages($locale, $messages);
        }

        return $resolver;
    }

    /**
     * @param array<string, string> $messages error code => message
     */
    public function withMessages(string $locale, array $messages): self
    {
        $catalog = $this->messages;
        $catalog[$locale] = [...$catalog[$locale] ?? [], ...$messages];

        return new self($this->locale, $catalog);
    }

    #[\Override]
    public function resolve(string $errorCode, string $fallback, array $parameters = [], ?string $locale = null): string
    {
        $message = $this->messages[$locale ?? $this->locale][$errorCode] ?? $this->messages['en'][$errorCode] ?? $fallback;
        $replacements = [];

        /** @var mixed $value */
        foreach ($parameters as $name => $value) {
            $text = self::text($value);

            if ($text !== null) {
                $replacements['{' . $name . '}'] = $text;
            }
        }

        return strtr($message, $replacements);
    }

    private static function text(mixed $value): ?string
    {
        return match (true) {
            is_scalar($value) => self::scalar($value),
            $value === null => '',
            $value instanceof DateTimeInterface => $value->format($value->format('His') === '000000' ? 'Y-m-d' : 'Y-m-d H:i:s'),
            is_array($value) && array_is_list($value) => self::join($value),
            default => null,
        };
    }

    /**
     * @param list<mixed> $values
     */
    private static function join(array $values): ?string
    {
        $texts = [];

        foreach ($values as $value) {
            if (!is_scalar($value)) {
                return null;
            }

            $texts[] = self::scalar($value);
        }

        return implode(', ', $texts);
    }

    private static function scalar(bool|int|float|string $value): string
    {
        return is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
    }
}
