<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Conformance;

use KaririCode\Exception\ErrorCodeTable;
use KaririCode\Validator\Exception\ValidatorCode;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * ValidatorCode against the ecosystem registry of record, CODE-BLOCKS.md of
 * kariricode/exception, read from the installed package (ADR-004).
 */
#[CoversNothing]
final class CodeBlockRegistryTest extends TestCase
{
    private const string REGISTRY = __DIR__ . '/../../vendor/kariricode/exception/CODE-BLOCKS.md';

    private const int FIRST = 3200;

    private const int LAST = 3299;

    #[Test]
    public function validatorCodeIsAValidCodeTable(): void
    {
        $table = new ErrorCodeTable(ValidatorCode::class);

        self::assertSame('VAL', $table->prefix);
        self::assertSame([self::FIRST, self::LAST], [$table->block->first, $table->block->last]);
    }

    /**
     * The installed registry may predate the claim (QD-V433 open items):
     * the block is then free, and once listed it belongs to this package.
     */
    #[Test]
    public function theRegistryLeavesTheBlockToThisPackage(): void
    {
        $registry = file(self::REGISTRY, FILE_IGNORE_NEW_LINES);
        self::assertIsArray($registry);
        $rows = 0;

        foreach ($registry as $line) {
            if (preg_match('/\A\| (\d{4})–(\d{4}) \| ([A-Z0-9—]+) \| ([^|]+?) \| (released|claimed|proposed|reserved) \|/u', $line, $row) !== 1) {
                continue;
            }

            ++$rows;
            $overlaps = (int) $row[1] <= self::LAST && (int) $row[2] >= self::FIRST;
            $ours = $row[4] === 'kariricode/validator';

            if ($ours) {
                self::assertSame([(string) self::FIRST, (string) self::LAST, 'VAL'], [$row[1], $row[2], $row[3]], $line);
            }

            self::assertFalse($overlaps && !$ours, 'block taken by ' . $row[4]);
            self::assertFalse(!$ours && $row[3] === 'VAL', 'prefix VAL taken by ' . $row[4]);
        }

        self::assertGreaterThan(30, $rows, 'the registry table was read');
    }

    #[Test]
    public function codesFollowTheDocumentedLayout(): void
    {
        $ranges = [
            'rule definitions and the registry' => [3200, 3219],
            'settings and bindings' => [3220, 3229],
            'validation outcome' => [3230, 3239],
            'integrations' => [3240, 3249],
        ];

        foreach (ValidatorCode::cases() as $case) {
            $inRange = array_filter($ranges, static fn(array $range): bool => $case->value >= $range[0] && $case->value <= $range[1]);
            self::assertCount(1, $inRange, $case->name . ' sits in a documented range');
        }
    }
}
