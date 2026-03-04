<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Configuration;

use KaririCode\Validator\Configuration\ValidatorConfiguration;
use PHPUnit\Framework\TestCase;

final class ValidatorConfigurationTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = new ValidatorConfiguration();

        $this->assertSame('en', $config->defaultLocale);
        $this->assertFalse($config->failFast);
        $this->assertSame(0, $config->maxErrorsPerField);
        $this->assertSame([], $config->customMessages);
    }

    public function testCustomValues(): void
    {
        $config = new ValidatorConfiguration(
            defaultLocale: 'pt-BR',
            failFast: true,
            maxErrorsPerField: 3,
            customMessages: [
                'string.email' => ['pt-BR' => 'E-mail inválido.'],
            ],
        );

        $this->assertSame('pt-BR', $config->defaultLocale);
        $this->assertTrue($config->failFast);
        $this->assertSame(3, $config->maxErrorsPerField);
        $this->assertArrayHasKey('string.email', $config->customMessages);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(ValidatorConfiguration::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
