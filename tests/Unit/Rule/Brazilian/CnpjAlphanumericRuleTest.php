<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Unit\Rule\Brazilian;

use KaririCode\Validator\Core\ValidationContextImpl;
use KaririCode\Validator\Rule\Brazilian\CnpjRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive test suite for the updated CnpjRule.
 *
 * Covers both the legacy numeric CNPJ format and the new alphanumeric
 * format introduced by IN RFB nº 2.229/2024 (effective July 2026).
 *
 * Test values are computed using the official Módulo 11 algorithm:
 *   - Digits: face value
 *   - Letters: ord(CHAR) - 48  (A=17, B=18, … Z=42)
 *   - Check digits remain numeric
 *
 * @package KaririCode\Validator\Tests\Unit\Rule\Brazilian
 */
#[CoversClass(CnpjRule::class)]
final class CnpjAlphanumericRuleTest extends TestCase
{
    private function ctx(): ValidationContextImpl
    {
        return ValidationContextImpl::create([])->withField('cnpj');
    }

    // ── Legacy numeric format ────────────────────────────────────────────────

    public function testLegacyFormattedValidCnpj(): void
    {
        $rule = new CnpjRule();
        $this->assertTrue($rule->validate('11.222.333/0001-81', $this->ctx()));
        $this->assertTrue($rule->validate('12.345.678/0001-95', $this->ctx()));
    }

    public function testLegacyUnformattedValidCnpj(): void
    {
        $rule = new CnpjRule();
        $this->assertTrue($rule->validate('11222333000181', $this->ctx()));
        $this->assertTrue($rule->validate('12345678000195', $this->ctx()));
    }

    public function testLegacyRejectsAllSameDigits(): void
    {
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('11111111111111', $this->ctx()));
        $this->assertFalse($rule->validate('00000000000000', $this->ctx()));
        $this->assertFalse($rule->validate('99999999999999', $this->ctx()));
    }

    public function testLegacyRejectsWrongCheckDigit(): void
    {
        $rule = new CnpjRule();
        // Valid is 81, wrong second digit
        $this->assertFalse($rule->validate('11222333000182', $this->ctx()));
        // Valid DV is 95, wrong first digit
        $this->assertFalse($rule->validate('12345678000185', $this->ctx()));
    }

    public function testLegacyRejectsWrongLength(): void
    {
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('1234567890123', $this->ctx()));   // 13 digits
        $this->assertFalse($rule->validate('123456789012345', $this->ctx())); // 15 digits
        $this->assertFalse($rule->validate('', $this->ctx()));
    }

    // ── Alphanumeric format (IN RFB nº 2.229/2024) ─────────────────────────

    /**
     * Valid alphanumeric CNPJs — computed using official Módulo 11 algorithm.
     * Format: 12 alphanumeric body chars + 2 numeric check digits.
     *
     * - AB3DE6GHJK0041  : mixed letters and digits
     * - AA000000000108  : mostly zeros with letter prefix
     * - 12ABC345000188  : mostly digits with middle letters
     */
    public function testAlphanumericValidCnpj(): void
    {
        $rule = new CnpjRule();
        $this->assertTrue($rule->validate('AB3DE6GHJK0041', $this->ctx()));
        $this->assertTrue($rule->validate('AA000000000108', $this->ctx()));
        $this->assertTrue($rule->validate('12ABC345000188', $this->ctx()));
    }

    public function testAlphanumericFormattedValidCnpj(): void
    {
        $rule = new CnpjRule();
        // Formatted: XX.XXX.XXX/XXXX-DD — dots, slash, hyphen stripped for check
        $this->assertTrue($rule->validate('AB.3DE.6GH/JK00-41', $this->ctx()));
        $this->assertTrue($rule->validate('AA.000.000/0001-08', $this->ctx()));
    }

    public function testAlphanumericRejectsForbiddenCharI(): void
    {
        // 'I' is visually ambiguous with '1'
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('IB3DE6GHJK0041', $this->ctx())); // I at pos 0
        $this->assertFalse($rule->validate('AB3DE6IHJK0041', $this->ctx())); // I at pos 6
    }

    public function testAlphanumericRejectsForbiddenCharO(): void
    {
        // 'O' is visually ambiguous with '0'
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('OB3DE6GHJK0041', $this->ctx())); // O at pos 0
        $this->assertFalse($rule->validate('AB3DE6OHJK0041', $this->ctx())); // O at pos 6
    }

    public function testAlphanumericRejectsForbiddenCharQ(): void
    {
        // 'Q' excluded per spec
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('QB3DE6GHJK0041', $this->ctx())); // Q at pos 0
        $this->assertFalse($rule->validate('AB3DE6QHJK0041', $this->ctx())); // Q at pos 6
    }

    public function testAlphanumericRejectsLetterInCheckDigits(): void
    {
        // Positions 13–14 must always be numeric digits
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('AB3DE6GHJK00A1', $this->ctx())); // letter at pos 12
        $this->assertFalse($rule->validate('AB3DE6GHJK004B', $this->ctx())); // letter at pos 13
    }

    public function testAlphanumericRejectsWrongCheckDigit(): void
    {
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('AB3DE6GHJK0099', $this->ctx())); // valid body, wrong DV
        $this->assertFalse($rule->validate('AA000000000199', $this->ctx())); // valid body, wrong DV
    }

    public function testAlphanumericRejectsSpecialCharInBody(): void
    {
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate('AB3D-6GHJK0041', $this->ctx())); // hyphen in body
        $this->assertFalse($rule->validate('AB3D@6GHJK0041', $this->ctx())); // @ in body
    }

    // ── Input type and format guards ─────────────────────────────────────────

    public function testRejectsNonStringInput(): void
    {
        $rule = new CnpjRule();
        $this->assertFalse($rule->validate(11222333000181, $this->ctx()));
        $this->assertFalse($rule->validate(null, $this->ctx()));
        $this->assertFalse($rule->validate([], $this->ctx()));
        $this->assertFalse($rule->validate(true, $this->ctx()));
    }

    // ── Data-provider: comprehensive valid cases ──────────────────────────────

    /** @return array<string, array{string, bool}> */
    public static function cnpjProvider(): array
    {
        return [
            // Legacy valid
            'legacy_formatted_1'       => ['11.222.333/0001-81', true],
            'legacy_formatted_2'       => ['12.345.678/0001-95', true],
            'legacy_unformatted_1'     => ['11222333000181', true],
            'legacy_unformatted_2'     => ['12345678000195', true],
            // Legacy invalid
            'legacy_all_zeros'         => ['00000000000000', false],
            'legacy_all_ones'          => ['11111111111111', false],
            'legacy_wrong_dv'          => ['11222333000182', false],
            'legacy_too_short'         => ['1122233300018', false],
            // Alphanumeric valid
            'alpha_AB3DE6GHJK0041'     => ['AB3DE6GHJK0041', true],
            'alpha_AA000000000108'     => ['AA000000000108', true],
            'alpha_12ABC345000188'     => ['12ABC345000188', true],
            'alpha_formatted'          => ['AB.3DE.6GH/JK00-41', true],
            // Alphanumeric invalid
            'alpha_forbidden_I'        => ['IB3DE6GHJK0041', false],
            'alpha_forbidden_O'        => ['OB3DE6GHJK0041', false],
            'alpha_forbidden_Q'        => ['QB3DE6GHJK0041', false],
            'alpha_letter_check_digit' => ['AB3DE6GHJK00A1', false],
            'alpha_wrong_dv'           => ['AB3DE6GHJK0099', false],
        ];
    }

    #[DataProvider('cnpjProvider')]
    public function testCnpjValidation(string $cnpj, bool $expected): void
    {
        $this->assertSame($expected, (new CnpjRule())->validate($cnpj, $this->ctx()));
    }

    // ── Contract ─────────────────────────────────────────────────────────────

    public function testGetErrorCode(): void
    {
        $this->assertSame('brazilian.cnpj', (new CnpjRule())->getErrorCode());
    }

    public function testGetDefaultMessage(): void
    {
        $this->assertSame('The {field} is not a valid CNPJ.', (new CnpjRule())->getDefaultMessage());
    }
}
