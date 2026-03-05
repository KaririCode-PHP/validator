<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Core;

use KaririCode\Validator\Core\DefaultMessageResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultMessageResolver::class)]
final class DefaultMessageResolverTest extends TestCase
{
    public function testResolvesEnglishByDefault(): void
    {
        $resolver = new DefaultMessageResolver();
        $msg = $resolver->resolve('logic.required', ['field' => 'email']);
        $this->assertSame('The email field is required.', $msg);
    }

    public function testResolvesBrazilianPortuguese(): void
    {
        $resolver = new DefaultMessageResolver('pt_BR');
        $msg = $resolver->resolve('logic.required', ['field' => 'email']);
        $this->assertSame('O campo email é obrigatório.', $msg);
    }

    public function testFallsBackToEnglishForMissingLocale(): void
    {
        $resolver = new DefaultMessageResolver('fr');
        $msg = $resolver->resolve('logic.required', ['field' => 'name']);
        $this->assertSame('The name field is required.', $msg);
    }

    public function testFallsBackToGenericForUnknownCode(): void
    {
        $resolver = new DefaultMessageResolver();
        $msg = $resolver->resolve('unknown.code');
        $this->assertSame('Validation failed for unknown.code.', $msg);
    }

    public function testCustomMessageOverride(): void
    {
        $resolver = new DefaultMessageResolver();
        $resolver->addMessage('en', 'logic.required', 'Field {field} is mandatory!');
        $msg = $resolver->resolve('logic.required', ['field' => 'name']);
        $this->assertSame('Field name is mandatory!', $msg);
    }

    public function testBatchMessages(): void
    {
        $resolver = new DefaultMessageResolver();
        $resolver->addMessages('de', [
            'logic.required' => '{field} ist erforderlich.',
        ]);
        $msg = $resolver->resolve('logic.required', ['field' => 'Name'], 'de');
        $this->assertSame('Name ist erforderlich.', $msg);
    }
}
