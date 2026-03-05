<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\CrossField;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\CrossField\DifferentFromRule;
use KaririCode\Validator\Rule\CrossField\RequiredWithRule;
use KaririCode\Validator\Rule\CrossField\RequiredWithoutRule;
use KaririCode\Validator\Rule\CrossField\SameAsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SameAsRule::class)]
#[CoversClass(DifferentFromRule::class)]
#[CoversClass(RequiredWithRule::class)]
#[CoversClass(RequiredWithoutRule::class)]
final class CrossFieldRulesTest extends TestCase
{
    private function ctx(array $rootData, array $params): ValidationContextImpl
    {
        return ValidationContextImpl::create($rootData)->withField('test')->withParameters($params);
    }

    // ── SameAs ──
    public function testSameAsMatchingFields(): void
    {
        $rule = new SameAsRule();
        $ctx = $this->ctx(['password' => 'secret123'], ['other' => 'password']);
        $this->assertTrue($rule->validate('secret123', $ctx));
    }

    public function testSameAsMismatch(): void
    {
        $rule = new SameAsRule();
        $ctx = $this->ctx(['password' => 'secret123'], ['other' => 'password']);
        $this->assertFalse($rule->validate('different', $ctx));
    }

    public function testSameAsOtherFieldMissing(): void
    {
        $rule = new SameAsRule();
        $ctx = $this->ctx([], ['other' => 'password']);
        $this->assertFalse($rule->validate('secret', $ctx));
    }

    // ── DifferentFrom ──
    public function testDifferentFromDifferentValues(): void
    {
        $rule = new DifferentFromRule();
        $ctx = $this->ctx(['old_password' => 'old123'], ['other' => 'old_password']);
        $this->assertTrue($rule->validate('new456', $ctx));
    }

    public function testDifferentFromSameValues(): void
    {
        $rule = new DifferentFromRule();
        $ctx = $this->ctx(['old_password' => 'same'], ['other' => 'old_password']);
        $this->assertFalse($rule->validate('same', $ctx));
    }

    public function testDifferentFromOtherAbsent(): void
    {
        $rule = new DifferentFromRule();
        $ctx = $this->ctx([], ['other' => 'old_password']);
        $this->assertTrue($rule->validate('anything', $ctx));
    }

    // ── RequiredWith ──
    public function testRequiredWithBothPresent(): void
    {
        $rule = new RequiredWithRule();
        $ctx = $this->ctx(['phone' => '+5511999'], ['other' => 'phone']);
        $this->assertTrue($rule->validate('some-value', $ctx));
    }

    public function testRequiredWithOtherPresentThisEmpty(): void
    {
        $rule = new RequiredWithRule();
        $ctx = $this->ctx(['phone' => '+5511999'], ['other' => 'phone']);
        $this->assertFalse($rule->validate('', $ctx));
        $this->assertFalse($rule->validate(null, $ctx));
    }

    public function testRequiredWithOtherAbsent(): void
    {
        $rule = new RequiredWithRule();
        $ctx = $this->ctx([], ['other' => 'phone']);
        $this->assertTrue($rule->validate('', $ctx)); // Other absent → rule doesn't apply
    }

    // ── RequiredWithout ──
    public function testRequiredWithoutOtherAbsentThisPresent(): void
    {
        $rule = new RequiredWithoutRule();
        $ctx = $this->ctx([], ['other' => 'phone']);
        $this->assertTrue($rule->validate('email@test.com', $ctx));
    }

    public function testRequiredWithoutOtherAbsentThisEmpty(): void
    {
        $rule = new RequiredWithoutRule();
        $ctx = $this->ctx([], ['other' => 'phone']);
        $this->assertFalse($rule->validate('', $ctx));
    }

    public function testRequiredWithoutOtherPresent(): void
    {
        $rule = new RequiredWithoutRule();
        $ctx = $this->ctx(['phone' => '+5511999'], ['other' => 'phone']);
        $this->assertTrue($rule->validate('', $ctx)); // Other present → rule doesn't apply
    }

    // ── Error codes ──
    public function testErrorCodes(): void
    {
        $this->assertSame('crossfield.same_as', (new SameAsRule())->getErrorCode());
        $this->assertSame('crossfield.different_from', (new DifferentFromRule())->getErrorCode());
        $this->assertSame('crossfield.required_with', (new RequiredWithRule())->getErrorCode());
        $this->assertSame('crossfield.required_without', (new RequiredWithoutRule())->getErrorCode());
    }
}
