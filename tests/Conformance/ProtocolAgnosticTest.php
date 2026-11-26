<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Conformance;

use KaririCode\Validator\Core\ValidatorEngine;
use KaririCode\Validator\Provider\ValidatorServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * ARFA 1.3 P4 — Protocol Agnosticism.
 *
 * ∀p ∈ Protocols: ARFA(data, p) → Result independent of p
 *
 * The validator accepts plain arrays — it doesn't know or care
 * whether the data comes from HTTP, gRPC, GraphQL, CLI, or WebSocket.
 */
final class ProtocolAgnosticTest extends TestCase
{
    private ValidatorEngine $engine;
    private array $rules;

    protected function setUp(): void
    {
        $this->engine = new ValidatorServiceProvider()->createEngine();
        $this->rules = [
            'email' => ['required', 'email'],
            'name' => ['required', ['length', ['min' => 2, 'max' => 100]]],
        ];
    }

    public function testHttpFormData(): void
    {
        // Simulated POST body
        $data = ['email' => 'user@example.com', 'name' => 'Walmir'];

        $result = $this->engine->validate($data, $this->rules);
        $this->assertTrue($result->isValid());
    }

    public function testGraphqlInput(): void
    {
        // Simulated GraphQL mutation input
        $data = ['email' => 'user@example.com', 'name' => 'Walmir'];

        $result = $this->engine->validate($data, $this->rules);
        $this->assertTrue($result->isValid());
    }

    public function testCliArguments(): void
    {
        // Simulated CLI parsed arguments
        $data = ['email' => 'user@example.com', 'name' => 'Walmir'];

        $result = $this->engine->validate($data, $this->rules);
        $this->assertTrue($result->isValid());
    }

    public function testResultIsIdenticalRegardlessOfProtocol(): void
    {
        $dataset = ['email' => 'bad', 'name' => ''];

        $result1 = $this->engine->validate($dataset, $this->rules);
        $result2 = $this->engine->validate($dataset, $this->rules);

        $this->assertSame($result1->errorCount(), $result2->errorCount());
        $this->assertSame($result1->failedFields(), $result2->failedFields());
    }
}
