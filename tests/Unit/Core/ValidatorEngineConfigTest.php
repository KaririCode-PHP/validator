<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use KaririCode\Validator\Configuration\ValidatorConfiguration;
use KaririCode\Validator\Provider\ValidatorServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ValidatorEngine configuration integration (failFast, maxErrors).
 */
final class ValidatorEngineConfigTest extends TestCase
{
    public function testFailFastStopsOnFirstErrorPerField(): void
    {
        $config = new ValidatorConfiguration(failFast: true);
        $engine = (new ValidatorServiceProvider())->createEngine($config);

        $result = $engine->validate(
            ['email' => ''],
            ['email' => ['required', 'email', ['length', ['min' => 5]]]],
        );

        // failFast: only 1 error per field
        $this->assertSame(1, $result->errorCount());
        $this->assertSame('logic.required', $result->getErrors()[0]->errorCode);
    }

    public function testMaxErrorsPerFieldLimitsCollection(): void
    {
        $config = new ValidatorConfiguration(maxErrorsPerField: 2);
        $engine = (new ValidatorServiceProvider())->createEngine($config);

        $result = $engine->validate(
            ['email' => ''],
            ['email' => ['required', 'not_empty', 'email', ['length', ['min' => 5]]]],
        );

        $this->assertSame(2, $result->errorCount());
    }

    public function testDefaultConfigCollectsAllErrors(): void
    {
        $engine = (new ValidatorServiceProvider())->createEngine();

        $result = $engine->validate(
            ['email' => ''],
            ['email' => ['required', 'not_empty', 'email']],
        );

        $this->assertSame(3, $result->errorCount());
    }

    public function testConfigurationAccessor(): void
    {
        $config = new ValidatorConfiguration(failFast: true, defaultLocale: 'pt_BR');
        $engine = (new ValidatorServiceProvider())->createEngine($config);

        $this->assertTrue($engine->getConfiguration()->failFast);
        $this->assertSame('pt_BR', $engine->getConfiguration()->defaultLocale);
    }
}
