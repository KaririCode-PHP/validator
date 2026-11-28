<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use DateTimeImmutable;
use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Core\DefaultMessageResolver;
use KaririCode\Validator\Core\InMemoryRuleRegistry;
use KaririCode\Validator\Core\MessageCatalog;
use KaririCode\Validator\Core\RuleContext;
use KaririCode\Validator\Exception\InvalidRuleException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

// ── Unit: MessagesTest ───────────────────────────────────────────────
// The catalog against the rules, and how messages are resolved and filled.
// ─────────────────────────────────────────────────────────────────────

#[CoversClass(DefaultMessageResolver::class)]
#[CoversClass(MessageCatalog::class)]
final class MessagesTest extends TestCase
{
    /**
     * Every built-in rule reports a code the three locales know, with the
     * English message as its fallback, under every bound combination.
     */
    #[Test]
    public function theCatalogCoversEveryBuiltInRuleInEveryLocale(): void
    {
        $registry = InMemoryRuleRegistry::withDefaults();
        $parameterSets = [[], ['min' => 1], ['max' => 1], ['min' => 1, 'max' => 2]];
        $codes = [];

        foreach (array_diff($registry->aliases(), ['nullable']) as $alias) {
            foreach ($parameterSets as $parameters) {
                try {
                    $violation = $registry->resolve($alias)->violation(new RuleContext(field: 'f', parameters: $parameters));
                } catch (InvalidRuleException) {
                    continue;
                }

                $codes[] = $violation->code;

                foreach (['en', 'pt_BR', 'es'] as $locale) {
                    self::assertArrayHasKey($violation->code, MessageCatalog::MESSAGES[$locale], $locale . ' ' . $alias);
                }

                self::assertSame(MessageCatalog::MESSAGES['en'][$violation->code], $violation->message, $alias);
            }
        }

        $reported = array_values(array_unique($codes));
        $composite = ['logic.callback', 'logic.at_least_one_of', 'collection.each'];
        sort($reported);
        $catalog = array_values(array_diff(array_keys(MessageCatalog::MESSAGES['en']), $composite));
        sort($catalog);

        self::assertSame($catalog, $reported, 'the codes of the composite rules are the only ones no alias reports');
    }

    #[Test]
    public function translationsUseTheSamePlaceholdersAsEnglish(): void
    {
        self::assertSame(['en', 'pt_BR', 'es'], array_keys(MessageCatalog::MESSAGES));

        foreach (MessageCatalog::MESSAGES['en'] as $code => $english) {
            preg_match_all('/\{\w+\}/', $english, $expected);
            sort($expected[0]);

            foreach (['pt_BR', 'es'] as $locale) {
                preg_match_all('/\{\w+\}/', MessageCatalog::MESSAGES[$locale][$code], $found);
                sort($found[0]);
                self::assertSame($expected[0], $found[0], $locale . ' ' . $code);
            }
        }
    }

    #[Test]
    public function messagesFallBackFromTheLocaleToEnglishToTheRule(): void
    {
        $resolver = (new DefaultMessageResolver('pt_BR'))->withMessages('pt_BR', ['custom.only_pt' => 'Só em português.']);

        self::assertSame('O campo email deve ser um endereço de e-mail válido.', $resolver->resolve(errorCode: 'string.email', fallback: 'x', parameters: ['field' => 'email']));
        self::assertSame('The email must be a valid email address.', $resolver->resolve(errorCode: 'string.email', fallback: 'x', parameters: ['field' => 'email'], locale: 'fr'));
        self::assertSame('Só em português.', $resolver->resolve(errorCode: 'custom.only_pt', fallback: 'x'));
        self::assertSame('x', $resolver->resolve(errorCode: 'custom.only_pt', fallback: 'x', locale: 'en'));
        self::assertSame('The age must be even.', $resolver->resolve(errorCode: 'custom.even', fallback: 'The {field} must be even.', parameters: ['field' => 'age']));
        self::assertSame('en', (new DefaultMessageResolver())->locale);
        self::assertSame(MessageCatalog::MESSAGES, (new DefaultMessageResolver())->messages);
    }

    #[Test]
    public function withMessagesAddsToALocaleWithoutChangingTheOriginal(): void
    {
        $original = new DefaultMessageResolver();
        $changed = $original->withMessages('en', ['string.email' => 'Bad {field}.'])->withMessages('fr', ['logic.required' => '{field} est requis.']);

        self::assertSame('Bad email.', $changed->resolve(errorCode: 'string.email', fallback: 'x', parameters: ['field' => 'email']));
        self::assertSame('The email must be a valid email address.', $original->resolve(errorCode: 'string.email', fallback: 'x', parameters: ['field' => 'email']));
        self::assertSame('nom est requis.', $changed->resolve(errorCode: 'logic.required', fallback: 'x', parameters: ['field' => 'nom'], locale: 'fr'));
        self::assertSame('The nom must be a valid URL.', $changed->resolve(errorCode: 'string.url', fallback: 'x', parameters: ['field' => 'nom'], locale: 'fr'));
        self::assertSame('en', $changed->locale);
    }

    #[Test]
    public function theConfigurationSetsTheLocaleAndCustomMessages(): void
    {
        $resolver = DefaultMessageResolver::fromConfiguration(new ValidatorConfiguration(
            defaultLocale: 'es',
            customMessages: ['es' => ['custom.even' => '{field} debe ser par.'], 'en' => ['custom.even' => '{field} must be even.']],
        ));

        self::assertSame('es', $resolver->locale);
        self::assertSame('edad debe ser par.', $resolver->resolve(errorCode: 'custom.even', fallback: 'x', parameters: ['field' => 'edad']));
        self::assertSame('age must be even.', $resolver->resolve(errorCode: 'custom.even', fallback: 'x', parameters: ['field' => 'age'], locale: 'en'));
        self::assertSame('El campo edad es obligatorio.', $resolver->resolve(errorCode: 'logic.required', fallback: 'x', parameters: ['field' => 'edad']));
    }

    #[Test]
    public function placeholdersAreFilledByType(): void
    {
        $message = '{s}|{i}|{f}|{t}|{b}|{n}|{d}|{dt}|{l}|{nested}|{map}|{o}|{missing}';
        $filled = (new DefaultMessageResolver())->resolve(errorCode: 'custom.types', fallback: $message, parameters: [
            's' => 'text',
            'i' => 3,
            'f' => 1.5,
            't' => true,
            'b' => false,
            'n' => null,
            'd' => new DateTimeImmutable('2026-11-28'),
            'dt' => new DateTimeImmutable('2026-11-28 09:05:01'),
            'l' => ['a', 1, false],
            'nested' => ['a', ['b']],
            'map' => ['k' => 'v'],
            'o' => new stdClass(),
        ]);

        self::assertSame('text|3|1.5|true|false||2026-11-28|2026-11-28 09:05:01|a, 1, false|{nested}|{map}|{o}|{missing}', $filled);
    }
}
