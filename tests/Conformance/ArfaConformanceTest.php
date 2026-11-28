<?php

declare(strict_types=1);

namespace KaririCode\Validator\Tests\Conformance;

use JsonException;
use KaririCode\Contract\Exception\Configuration;
use KaririCode\Contract\Exception\ContainerFailure;
use KaririCode\Contract\Exception\NotFound;
use KaririCode\Contract\Exception\Protocol;
use KaririCode\Contract\Exception\Runtime;
use KaririCode\Contract\Exception\Validation;
use KaririCode\Validator\Exception\ValidatorCode;
use KaririCode\Validator\Exception\ValidatorException;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpToken;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Executable checks of the ARFA 1.3 and Spec V4.3.3 rules the package
 * claims, so conformance is measured rather than asserted in prose.
 */
#[CoversNothing]
final class ArfaConformanceTest extends TestCase
{
    private const string ROOT = __DIR__ . '/../..';

    private const string SOURCE_NAMESPACE = 'KaririCode\\Validator\\';

    private const int ERROR_CODE_MIN = 3200;

    private const int ERROR_CODE_MAX = 3299;

    private const array FAMILIES = [Validation::class, NotFound::class, Configuration::class, Protocol::class, ContainerFailure::class, Runtime::class];

    /** The concrete exceptions of the package, each built by named constructors only. */
    private const array EXCEPTIONS = ['ConfigurationException', 'InvalidRuleException', 'UnsupportedInputException', 'ValidationException'];

    /**
     * Accepted deviation: the objects kariricode/contract's Inspection
     * ports pass around, any DTO and any attribute (kariricode/property-inspector
     * ADR-008; ADR-003 here).
     */
    private const array OBJECT_PARAMETERS = [
        'KaririCode\Validator\Core\AttributeRules::handleAttribute',
        'KaririCode\Validator\Core\AttributeValidator::validate',
    ];

    private const int MARKER_WIDTH = 72;

    private const array ADR_SECTIONS = [
        '## Status',
        '## Date',
        '## Context',
        '## Decision',
        '## Consequences',
        '## Alternatives Considered',
        '## References',
    ];

    /** Records written before Spec V4.3.3: the changelog's history of the tarball. */
    private const array HISTORICAL_RECORDS = ['CHANGELOG.md'];

    /** Comment criteria C2–C5 of Spec V4.3.3 §17; C1 is the summary sentence that opens the docblock. */
    private const array COMMENT_MARKERS = ['Preconditions:', 'Thread-safety:', 'Side effects:', 'Example', 'References:'];

    /** The eight gates of Spec V4.3.3 §29.1. */
    private const array QUALITY_GATES = ['test', 'test-conformance', 'phpstan', 'psalm', 'cs-check', 'complexity', 'mutation', 'security'];

    /** Values: results, events, settings, attributes and the rule context. */
    private const array VALUE_TYPES = [
        'Attribute/Validate.php', 'Attribute/ValidationGroup.php',
        'Configuration/ValidatorConfiguration.php',
        'Core/RuleContext.php',
        'Event/ValidationCompletedEvent.php', 'Event/ValidationStartedEvent.php',
        'Result/ValidationError.php', 'Result/ValidationResult.php', 'Result/Violation.php',
    ];

    /** Where each kariricode/contract and kariricode/exception namespace may be imported (§31). */
    private const array IMPORTS = [
        'KaririCode\\Exception' => ['Exception/'],
        'KaririCode\\Contract\\Inspection' => ['Core/AttributeRules.php', 'Core/AttributeValidator.php', 'Provider/'],
        'KaririCode\\Contract\\DI' => ['Provider/'],
        'KaririCode\\Contract\\Processor' => ['Integration/'],
        'KaririCode\\Contract\\Validator' => ['Integration/'],
    ];

    // ── Files ────────────────────────────────────────────────────────────

    #[Test]
    public function everySourceFileIsStrictAndDeclaresExactlyOneNamedType(): void
    {
        foreach (self::sourceFiles() as $relative => $code) {
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);\n", $code, $relative);
            self::assertSame(1, self::namedTypeDeclarations($code), $relative);
            self::assertSame(basename($relative, '.php'), self::reflect($relative)->getShortName(), $relative);
        }
    }

    #[Test]
    public function docblocksMeetTheFiveCommentCriteria(): void
    {
        foreach (self::sourceFiles() as $relative => $code) {
            $doc = (string) self::reflect($relative)->getDocComment();
            $summary = trim((string) preg_replace('#^/\*\*\s*\*\s*#', '', $doc));

            self::assertNotSame('', $doc, $relative . ': C1 role');
            self::assertDoesNotMatchRegularExpression('/^(Represents|This (class|interface|enum|trait)|Class|Interface)\b/', $summary, $relative . ': C1 states a role, not a restatement');

            foreach (self::COMMENT_MARKERS as $marker) {
                self::assertStringContainsString($marker, $doc, $relative);
            }

            self::assertDoesNotMatchRegularExpression('/@(package|author|license)\b/', $code, $relative);
        }
    }

    #[Test]
    public function sectionMarkersAreSeventyTwoColumnsWide(): void
    {
        foreach ([...self::sourceFiles(), ...self::phpFiles(self::ROOT . '/tests')] as $relative => $code) {
            foreach (explode("\n", $code) as $number => $line) {
                if (str_starts_with(ltrim($line), '// ─')) {
                    self::assertSame(self::MARKER_WIDTH, mb_strlen(ltrim($line)), sprintf('%s:%d', $relative, $number + 1));
                }
            }
        }
    }

    // ── Types ────────────────────────────────────────────────────────────

    #[Test]
    public function concreteClassesAreFinal(): void
    {
        foreach (self::sourceFiles() as $relative => $code) {
            $type = self::reflect($relative);

            if ($type->isInterface() || $type->isEnum() || $type->isTrait() || $type->isAbstract()) {
                continue;
            }

            self::assertTrue($type->isFinal(), $relative);
        }
    }

    #[Test]
    public function valuesAreFinalReadonly(): void
    {
        foreach (self::VALUE_TYPES as $relative) {
            $type = self::reflect($relative);
            self::assertTrue($type->isFinal() && $type->isReadOnly(), $relative);
        }

        self::assertCount(9, self::VALUE_TYPES);
    }

    #[Test]
    public function readonlyClassesDeclareNoPropertyHooksPerErrataE040(): void
    {
        foreach (self::sourceFiles() as $relative => $code) {
            $type = self::reflect($relative);

            if (!$type->isReadOnly()) {
                continue;
            }

            foreach ($type->getProperties() as $property) {
                self::assertFalse($property->hasHooks(), $relative . '::$' . $property->getName());
            }

            self::assertSame(1, self::countTokens($code, T_READONLY), $relative . ': readonly repeated inside a readonly class');
        }
    }

    #[Test]
    public function classConstantsAreTyped(): void
    {
        foreach (self::sourceFiles() as $relative => $code) {
            foreach (self::reflect($relative)->getReflectionConstants() as $constant) {
                if ($constant->isEnumCase()) {
                    continue;
                }

                self::assertTrue($constant->hasType(), $relative . '::' . $constant->getName());
            }
        }
    }

    #[Test]
    public function inheritanceInsideThePackageIsAtMostTwoLevelsDeep(): void
    {
        foreach (self::sourceFiles() as $relative => $code) {
            $depth = 1;

            for ($parent = self::reflect($relative)->getParentClass(); $parent !== false; $parent = $parent->getParentClass()) {
                if (str_starts_with($parent->getName(), self::SOURCE_NAMESPACE)) {
                    ++$depth;
                }
            }

            self::assertLessThanOrEqual(2, $depth, $relative);
        }
    }

    #[Test]
    public function noTypeKeepsMutableStaticState(): void
    {
        foreach (self::sourceFiles() as $relative => $code) {
            foreach (self::reflect($relative)->getProperties() as $property) {
                self::assertFalse($property->isStatic(), $relative . '::$' . $property->getName());
            }

            self::assertSame(0, preg_match('/^\s*static \$/m', $code), $relative . ': static variable');
        }
    }

    #[Test]
    public function namesAndMethodsAvoidTheForbiddenPatterns(): void
    {
        $forbiddenMethods = ['toarray', 'fromarray', 'jsonserialize', 'tojson', '__get', '__set', '__isset', '__unset', '__call', '__callstatic'];

        foreach (self::sourceFiles() as $relative => $code) {
            $type = self::reflect($relative);
            self::assertDoesNotMatchRegularExpression('/(Interface|Manager|Helper|Util|Utils)\z/', $type->getShortName(), $relative);
            self::assertNotContains('JsonSerializable', $type->getInterfaceNames(), $relative);

            foreach ($type->getMethods() as $method) {
                self::assertNotContains(strtolower($method->getName()), $forbiddenMethods, $relative);
            }
        }
    }

    /**
     * Public data is read through properties: no public get method without
     * parameters and no set method with exactly one.
     */
    #[Test]
    public function publicMethodsAreNotAccessors(): void
    {
        foreach (self::sourceFiles() as $relative => $code) {
            $type = self::reflect($relative);

            foreach ($type->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $type->getName()) {
                    continue;
                }

                $where = $relative . '::' . $method->getName();
                self::assertFalse(preg_match('/\Aget[A-Z]/', $method->getName()) === 1 && $method->getNumberOfParameters() === 0, $where . ': getter');
                self::assertFalse(preg_match('/\Aset[A-Z]/', $method->getName()) === 1 && $method->getNumberOfParameters() === 1, $where . ': setter');
            }
        }
    }

    /** A pattern anchors its end with `\z`: `$` also matches before a trailing newline. */
    #[Test]
    public function regexesAnchorTheEndWithBackslashZ(): void
    {
        $patterns = 0;

        foreach (self::sourceFiles() as $relative => $code) {
            foreach (PhpToken::tokenize($code) as $token) {
                // Whole patterns only; the fragments of a pattern built at run time are left out.
                if ($token->is(T_CONSTANT_ENCAPSED_STRING) && preg_match("#\\A'/.*/[a-z]*'\\z#s", $token->text) === 1) {
                    ++$patterns;
                    self::assertDoesNotMatchRegularExpression("#\\$/[a-z]*'\\z#", $token->text, $relative);
                }
            }
        }

        self::assertGreaterThan(0, $patterns);
    }

    #[Test]
    public function callSitesWithThreeOrMoreArgumentsNameThem(): void
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $finder = new NodeFinder();
        $offenders = [];
        $checked = 0;

        foreach (self::sourceFiles() as $relative => $code) {
            foreach ($finder->findInstanceOf($parser->parse($code) ?? [], Node\Expr\CallLike::class) as $call) {
                if ($call->isFirstClassCallable() || count($call->getArgs()) < 3 || self::isExempt($call)) {
                    continue;
                }

                ++$checked;

                foreach ($call->getArgs() as $argument) {
                    if ($argument->name === null && !$argument->unpack) {
                        $offenders[] = sprintf('%s:%d', $relative, $call->getStartLine());

                        break;
                    }
                }
            }
        }

        self::assertGreaterThan(0, $checked);
        self::assertSame([], $offenders, 'Spec V4.3.3 §30: name the arguments of every call with three or more.');
    }

    #[Test]
    public function contractsAreInterfaces(): void
    {
        $checked = 0;

        foreach (self::sourceFiles() as $relative => $code) {
            if (str_starts_with($relative, 'Contract/')) {
                ++$checked;
                self::assertTrue(self::reflect($relative)->isInterface(), $relative);
            }
        }

        self::assertSame(4, $checked);
    }

    /** ARFA 1.3 E-003: only the code table is backed, because getCode() reads its integer. */
    #[Test]
    public function enumsArePureUnlessAnExternalContractNeedsABackingType(): void
    {
        $checked = 0;

        foreach (self::sourceFiles() as $relative => $code) {
            $name = self::reflect($relative)->getName();

            if (enum_exists($name)) {
                ++$checked;
                $backed = $relative === 'Exception/ValidatorCode.php';
                self::assertSame($backed, (new \ReflectionEnum($name))->isBacked(), $relative . ': E-003');

                if ($backed) {
                    self::assertStringContainsString('E-003', $code, $relative . ': the justification is written down');
                }
            }
        }

        self::assertSame(1, $checked);
    }

    #[Test]
    public function everyDirectoryIsANamespaceSegment(): void
    {
        foreach (['src', 'tests'] as $root) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(self::ROOT . '/' . $root, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST,
            );

            foreach ($iterator as $entry) {
                if ($entry instanceof \SplFileInfo && $entry->isDir()) {
                    self::assertMatchesRegularExpression('/\A[A-Z][A-Za-z0-9]*\z/', $entry->getFilename(), $entry->getPathname());
                }
            }
        }
    }

    // ── Errors ───────────────────────────────────────────────────────────

    #[Test]
    public function errorCodesAreUniqueAndInsideTheReservedBlock(): void
    {
        $codes = [];

        foreach (ValidatorCode::cases() as $case) {
            self::assertGreaterThanOrEqual(self::ERROR_CODE_MIN, $case->value);
            self::assertLessThanOrEqual(self::ERROR_CODE_MAX, $case->value);
            $codes[$case->value] = $case->name;
        }

        self::assertCount(10, $codes);
        self::assertSame('VAL', ValidatorCode::prefix());
        self::assertSame([self::ERROR_CODE_MIN, self::ERROR_CODE_MAX], [ValidatorCode::block()->first, ValidatorCode::block()->last]);
    }

    #[Test]
    public function exceptionsAreBuiltOnlyByTheirNamedConstructors(): void
    {
        foreach (self::sourceFiles() as $relative => $code) {
            foreach (self::EXCEPTIONS as $exception) {
                if ($relative === 'Exception/' . $exception . '.php') {
                    continue;
                }

                self::assertDoesNotMatchRegularExpression('/\bnew\s+' . $exception . '\s*\(/', $code, $relative . ' builds ' . $exception . ' directly');
            }

            self::assertDoesNotMatchRegularExpression('/\bthrow\s+new\s+\\?[A-Za-z]*(Exception|Error)\b/', $code, $relative . ': throws an uncoded exception');
        }
    }

    #[Test]
    public function everyConcreteExceptionBelongsToExactlyOneContractFamilyAndThePackage(): void
    {
        $checked = 0;

        foreach (self::sourceFiles() as $relative => $code) {
            $type = self::reflect($relative);

            if (!str_starts_with($relative, 'Exception/') || $type->isAbstract() || $type->isEnum() || $type->isInterface()) {
                continue;
            }

            ++$checked;
            $families = array_filter(self::FAMILIES, static fn(string $family): bool => $type->implementsInterface($family));
            self::assertCount(1, $families, $relative);
            self::assertTrue($type->implementsInterface(ValidatorException::class), $relative);
            $parent = $type->getParentClass();
            self::assertNotFalse($parent, $relative);
            self::assertStringStartsWith('KaririCode\\Exception\\', $parent->getName(), $relative . ': built on a kariricode/exception base');
        }

        self::assertSame(count(self::EXCEPTIONS), $checked);
    }

    // ── Package metadata and supply chain ────────────────────────────────

    #[Test]
    public function composerDeclaresArfaAsvsAndSpecVersions(): void
    {
        $composer = self::composer();

        self::assertSame(
            ['arfa' => ['version' => '1.3', 'level' => 1], 'asvs' => ['version' => '5.0', 'level' => 2], 'spec' => '4.3.3', 'error-code-range' => 'VAL 3200–3299'],
            self::at($composer, 'extra', 'kariricode'),
            'Spec V4.3.3 E-044 and gate G4',
        );

        // Spec V4.3.3 §31: PHP and the integrator packages only;
        // kariricode/property-inspector is a suggestion (ADR-003).
        self::assertSame(
            ['php' => '^8.4', 'kariricode/contract' => '^2.12', 'kariricode/exception' => '^2.0.2'],
            self::at($composer, 'require'),
        );
        self::assertSame(['kariricode/property-inspector'], array_keys((array) self::at($composer, 'suggest')));
        self::assertNull(self::at($composer, 'require-dev'), 'the toolchain comes from devkit.php (ADR-007)');

        foreach (['require', 'require-dev', 'suggest'] as $section) {
            self::assertNull(self::at($composer, $section, 'kariricode/framework'), $section);
        }
    }

    #[Test]
    public function ecosystemPackagesAreImportedOnlyWhereTheyBelong(): void
    {
        $found = [];
        $imports = 0;

        foreach (self::sourceFiles() as $relative => $code) {
            preg_match_all('/^use ([A-Za-z]+\\\\[A-Za-z]+(?:\\\\[A-Za-z]+)?)\\\\[A-Za-z\\\\]+(?: as [A-Za-z]+)?;/m', $code, $matches);

            foreach ($matches[1] as $namespace) {
                if (str_starts_with($namespace, 'KaririCode\\Validator')) {
                    continue;
                }

                ++$imports;
                $prefix = str_starts_with($namespace, 'KaririCode\\Exception') ? 'KaririCode\\Exception' : $namespace;
                $allowed = array_filter(self::IMPORTS[$prefix] ?? [], static fn(string $place): bool => str_starts_with($relative, $place));

                if ($allowed === []) {
                    $found[] = $relative . ': ' . $namespace;
                }
            }
        }

        self::assertGreaterThan(0, $imports);
        self::assertSame([], $found, 'the engine and the rules depend on nothing outside the package (§31)');
    }

    /** Rules hold no state, so one instance serves every field and request. */
    #[Test]
    public function rulesAreFinalReadonlyAndImplementTheContract(): void
    {
        $rules = 0;

        foreach (self::sourceFiles() as $relative => $code) {
            if (str_starts_with($relative, 'Rule/')) {
                ++$rules;
                $type = self::reflect($relative);
                self::assertTrue($type->isFinal() && $type->isReadOnly(), $relative);
                self::assertTrue($type->implementsInterface(\KaririCode\Validator\Contract\ValidationRule::class), $relative);
                self::assertStringEndsWith('Rule.php', $relative);
            }
        }

        self::assertSame(57, $rules);
    }

    /** Spec V4.3.3 §30: no `object` parameter outside the accepted deviation. */
    #[Test]
    public function objectParametersAreTheInspectionPortsAcceptedDeviation(): void
    {
        $found = [];

        foreach (self::sourceFiles() as $relative => $code) {
            foreach (self::reflect($relative)->getMethods() as $method) {
                foreach ($method->getParameters() as $parameter) {
                    $where = $method->getDeclaringClass()->getName() . '::' . $method->getName();

                    if ((string) $parameter->getType() === 'object' && !in_array(needle: $where, haystack: self::OBJECT_PARAMETERS, strict: true)) {
                        $found[] = $where . '($' . $parameter->getName() . ')';
                    }
                }
            }
        }

        self::assertSame([], $found);
    }

    #[Test]
    public function composerExposesTheQualityGates(): void
    {
        $scripts = self::at(self::composer(), 'scripts');
        self::assertIsArray($scripts);

        foreach ([...self::QUALITY_GATES, 'sbom', 'check'] as $gate) {
            self::assertArrayHasKey($gate, $scripts, 'composer ' . $gate);
        }
    }

    #[Test]
    public function supplyChainIsPinned(): void
    {
        self::assertDoesNotMatchRegularExpression('#^/?composer\.lock$#m', self::document('.gitignore'), 'composer.lock is committed (Spec V4.3.3 §32)');
        $workflows = self::glob('/.github/workflows/*.yml');
        self::assertNotEmpty($workflows);
        $actions = 0;

        foreach ($workflows as $workflow) {
            foreach (self::lines($workflow) as $number => $line) {
                $where = basename($workflow) . ':' . ($number + 1);

                if (preg_match('/^\s*(?:-\s*)?uses:\s*(\S+)/', $line, $match) === 1) {
                    ++$actions;
                    self::assertMatchesRegularExpression('/@[0-9a-f]{40}\z/', $match[1], $where . ': action pinned by commit SHA');
                }

                if (preg_match('/^\s*image:\s*(\S+)/', $line, $match) === 1) {
                    self::assertMatchesRegularExpression('/@sha256:[0-9a-f]{64}\z/', $match[1], $where . ': image pinned by digest');
                }
            }
        }

        self::assertGreaterThan(0, $actions);
    }

    // ── Documentation ────────────────────────────────────────────────────

    #[Test]
    public function documentationFollowsTheCanonicalLayout(): void
    {
        $adrs = self::glob('/docs/adr/ADR-*.md');
        self::assertCount(8, $adrs);

        foreach ($adrs as $path) {
            $adr = (string) file_get_contents($path);

            foreach (self::ADR_SECTIONS as $section) {
                self::assertMatchesRegularExpression('/^' . preg_quote($section, '/') . '$/m', $adr, basename($path));
            }
        }

        self::assertMatchesRegularExpression('/^- \*\*Status: (PENDING|PASS|FAIL)\*\*/m', self::document('docs/quality/QD-V433-COMPLIANCE.md'));

        foreach (['## Threat Model', '## ASVS 5.0', '## Supply Chain'] as $section) {
            self::assertStringContainsString($section, self::document('SECURITY.md'));
        }

        foreach (['## [3.0.0]', '### Spec V4.3.3 conformance (2026-11-28)', '**Breaking**', '**Fixed**'] as $heading) {
            self::assertStringContainsString($heading, self::document('CHANGELOG.md'));
        }
    }

    #[Test]
    public function documentationAndSourcesCiteTheCurrentSpec(): void
    {
        $texts = self::sourceFiles();

        foreach (['README.md', 'SECURITY.md', 'CONTRIBUTING.md', ...self::markdownUnder('docs')] as $relative) {
            if (!in_array(needle: $relative, haystack: self::HISTORICAL_RECORDS, strict: true)) {
                $texts[$relative] = self::document($relative);
            }
        }

        foreach ($texts as $relative => $text) {
            self::assertDoesNotMatchRegularExpression('/Spec V4\.0\b|Specification V4\.0\b|Directive V4\.0\b|\bE-013\b/', $text, $relative . ': cite Spec V4.3.3 and its errata numbers');
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /** @return list<string> paths relative to the root */
    private static function markdownUnder(string $directory): array
    {
        $root = (string) realpath(self::ROOT);
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/' . $directory, \FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->getExtension() === 'md') {
                $files[] = substr($file->getPathname(), strlen($root) + 1);
            }
        }

        sort($files);

        return $files;
    }

    /** @return list<string> */
    private static function glob(string $pattern): array
    {
        $paths = glob(self::ROOT . $pattern);

        return $paths === false ? [] : $paths;
    }

    /** @return list<string> */
    private static function lines(string $path): array
    {
        $lines = file($path);

        return $lines === false ? [] : $lines;
    }

    /** @return array<mixed> */
    private static function composer(): array
    {
        try {
            $composer = json_decode(self::document('composer.json'), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            self::fail('composer.json is not valid JSON: ' . $error->getMessage());
        }

        self::assertIsArray($composer);

        return $composer;
    }

    private static function at(mixed $data, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return null;
            }

            $data = $data[$key];
        }

        return $data;
    }

    private static function document(string $relative): string
    {
        $path = self::ROOT . '/' . $relative;
        self::assertFileExists($path);

        return (string) file_get_contents($path);
    }

    /** @return array<string, string> path relative to src/ => contents */
    private static function sourceFiles(): array
    {
        return self::phpFiles(self::ROOT . '/src');
    }

    /** @return array<string, string> */
    private static function phpFiles(string $directory): array
    {
        $files = [];
        $root = (string) realpath($directory);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->getExtension() === 'php') {
                $files[substr($file->getPathname(), strlen($root) + 1)] = (string) file_get_contents($file->getPathname());
            }
        }

        ksort($files);
        self::assertNotEmpty($files);

        return $files;
    }

    /** @return ReflectionClass<object> */
    private static function reflect(string $relative): ReflectionClass
    {
        $name = self::SOURCE_NAMESPACE . str_replace('/', '\\', substr($relative, 0, -4));
        self::assertTrue(class_exists($name) || interface_exists($name) || enum_exists($name) || trait_exists($name), $name);

        return new ReflectionClass($name);
    }

    private static function namedTypeDeclarations(string $code): int
    {
        $tokens = array_values(array_filter(PhpToken::tokenize($code), static fn(PhpToken $token): bool => !$token->isIgnorable()));
        $count = 0;

        foreach ($tokens as $index => $token) {
            $declares = $token->is([T_CLASS, T_INTERFACE, T_ENUM, T_TRAIT]);
            $previous = $tokens[$index - 1] ?? null;
            $next = $tokens[$index + 1] ?? null;

            if ($declares && $previous?->is([T_DOUBLE_COLON, T_NEW]) !== true && $next?->is(T_STRING) === true) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * The printf family, whose arguments follow the format, and calls of a
     * callable the application supplies (an invoker, a recorder), whose
     * parameter names the package cannot know.
     */
    private static function isExempt(Node\Expr\CallLike $call): bool
    {
        if (!$call instanceof Node\Expr\FuncCall) {
            return false;
        }

        return !$call->name instanceof Node\Name
            || in_array(needle: $call->name->toLowerString(), haystack: ['sprintf', 'printf', 'vsprintf'], strict: true);
    }

    private static function countTokens(string $code, int $kind): int
    {
        return count(array_filter(PhpToken::tokenize($code), static fn(PhpToken $token): bool => $token->is($kind)));
    }
}
