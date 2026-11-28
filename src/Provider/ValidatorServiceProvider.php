<?php

declare(strict_types=1);

namespace KaririCode\Validator\Provider;

use Closure;
use KaririCode\Contract\DI\BindableContainer;
use KaririCode\Contract\DI\ServiceProvider;
use KaririCode\Contract\Inspection\PropertyInspector;
use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Contract\MessageResolver;
use KaririCode\Validator\Contract\RuleRegistry;
use KaririCode\Validator\Core\AttributeValidator;
use KaririCode\Validator\Core\DefaultMessageResolver;
use KaririCode\Validator\Core\InMemoryRuleRegistry;
use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Exception\ConfigurationException;

// ── Provider: ValidatorServiceProvider ───────────────────────────────
// Registers the validator in a kariricode/contract container.
// ─────────────────────────────────────────────────────────────────────

/**
 * Binds, as singletons, the rule registry with the built-in rules, the
 * message resolver for the configured locale, the engine, and the
 * AttributeValidator. The AttributeValidator takes the PropertyInspector
 * the container holds, so bind one (kariricode/property-inspector's
 * ReflectionPropertyInspector) before resolving it.
 *
 * Preconditions: services are resolved after register(); a binding that
 * resolves to the wrong type throws ConfigurationException.
 *
 * Thread-safety: immutable; the container owns the instances.
 *
 * Side effects: register() adds bindings to the container.
 *
 * Example:
 *
 *     $provider = new ValidatorServiceProvider(new ValidatorConfiguration(defaultLocale: 'pt_BR'));
 *     $provider->register($container);
 *     $engine = $container->get(ValidatorEngine::class);
 *
 * References: kariricode/contract DI\ServiceProvider; ADR-006.
 */
final readonly class ValidatorServiceProvider implements ServiceProvider
{
    public function __construct(
        private ValidatorConfiguration $configuration = new ValidatorConfiguration(),
        private ?Closure $observer = null,
    ) {}

    #[\Override]
    public function register(BindableContainer $container): void
    {
        $container->bind(abstract: RuleRegistry::class, concrete: static fn(): RuleRegistry => InMemoryRuleRegistry::withDefaults(), singleton: true);
        $container->bind(abstract: MessageResolver::class, concrete: fn(): MessageResolver => DefaultMessageResolver::fromConfiguration($this->configuration), singleton: true);
        $container->bind(abstract: ValidatorEngine::class, concrete: fn(): ValidatorEngine => $this->engine($container), singleton: true);
        $container->bind(abstract: AttributeValidator::class, concrete: static fn(): AttributeValidator => new AttributeValidator(
            engine: self::service($container, ValidatorEngine::class),
            inspector: self::service($container, PropertyInspector::class),
        ), singleton: true);
    }

    #[\Override]
    public function boot(): void {}

    private function engine(BindableContainer $container): ValidatorEngine
    {
        return new ValidatorEngine(
            registry: self::service($container, RuleRegistry::class),
            configuration: $this->configuration,
            messages: self::service($container, MessageResolver::class),
            observer: $this->observer,
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    private static function service(BindableContainer $container, string $id): object
    {
        /** @var mixed $service */
        $service = $container->get($id);

        return $service instanceof $id ? $service : throw ConfigurationException::invalidBinding(id: $id, type: get_debug_type($service));
    }
}
