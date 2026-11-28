<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Support;

use Closure;
use KaririCode\Contract\DI\BindableContainer;
use LogicException;

// ── Support: MemoryContainer ─────────────────────────────────────────
// A kariricode/contract BindableContainer, enough for the provider tests.
// ─────────────────────────────────────────────────────────────────────

/**
 * Keeps bindings in memory: a callable is a factory, singletons are built
 * once, instance() stores a value as it is.
 */
final class MemoryContainer implements BindableContainer
{
    /** @var array<string, array{Closure(): mixed, bool}> */
    private array $bindings = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var list<string> ids bound as singletons */
    public private(set) array $singletons = [];

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        [$factory, $singleton] = $this->bindings[$id] ?? throw new LogicException('Nothing is bound to ' . $id . '.');
        $service = $factory();

        if ($singleton) {
            $this->instances[$id] = $service;
        }

        return $service;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances) || array_key_exists($id, $this->bindings);
    }

    public function bind(string $abstract, callable|string|null $concrete, bool $singleton = false): void
    {
        if (!is_callable($concrete)) {
            throw new LogicException('MemoryContainer binds factories only.');
        }

        $this->bindings[$abstract] = [Closure::fromCallable($concrete), $singleton];

        if ($singleton) {
            $this->singletons[] = $abstract;
        }
    }

    public function instance(string $abstract, mixed $value): void
    {
        $this->instances[$abstract] = $value;
    }
}
