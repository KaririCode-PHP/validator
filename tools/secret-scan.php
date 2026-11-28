<?php

declare(strict_types=1);

/*
 * Secret scan for `composer security` (Spec V4.3.3 §29.1 and §32): fails when
 * a text file holds something shaped like a credential. vendor/, build/ and
 * .git/ are skipped, and so is a line that carries the marker
 * "secret-scan:allow": a reviewed sample in a test or a document, such as a
 * DSN that interpolates a password reference.
 *
 * Usage: php tools/secret-scan.php [--self-test]
 * --self-test plants a fake key in a temporary directory and fails unless the
 * scan reports it, so a broken pattern cannot pass silently.
 */

$patterns = [
    'private key' => '/-----BEGIN (?:RSA |EC |DSA |OPENSSH |PGP |ENCRYPTED )?PRIVATE KEY-----/',
    'AWS access key id' => '/\b(?:AKIA|ASIA)[0-9A-Z]{16}\b/',
    'GitHub token' => '/\bgh[pousr]_[A-Za-z0-9]{36,}\b/',
    'Slack token' => '/\bxox[abposr]-[A-Za-z0-9-]{10,}\b/',
    'credential in a URL' => '#\b[a-z][a-z0-9+.-]*://[^\s:/@]+:[^\s/@]{3,}@#i',
    'hard-coded secret' => '/\b(?:password|passwd|secret|api[_-]?key|access[_-]?token|auth[_-]?token)\b\s*(?:=>|[:=])\s*[\'"][^\'"\s]{12,}[\'"]/i',
];

$scan = static function (string $root) use ($patterns): array {
    $findings = [];
    $files = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        static fn(SplFileInfo $entry): bool => !in_array($entry->getFilename(), ['vendor', 'build', '.git'], true),
    ));

    foreach ($files as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getSize() > 1_048_576) {
            continue;
        }

        $text = (string) file_get_contents($file->getPathname());

        if (str_contains($text, "\0")) {
            continue;
        }

        $lines = explode("\n", $text);

        foreach ($patterns as $label => $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE) > 0) {
                foreach ($matches[0] as [, $offset]) {
                    $line = substr_count($text, "\n", 0, $offset) + 1;

                    if (!str_contains($lines[$line - 1] ?? '', 'secret-scan:allow')) {
                        $findings[] = sprintf('%s:%d: %s', substr($file->getPathname(), strlen($root) + 1), $line, $label);
                    }
                }
            }
        }
    }

    return $findings;
};

if (in_array('--self-test', $argv, true)) {
    $sandbox = sys_get_temp_dir() . '/secret-scan-' . bin2hex(random_bytes(4));
    mkdir($sandbox);
    file_put_contents($sandbox . '/planted.env', 'AWS_KEY=AKIA' . strtoupper(bin2hex(random_bytes(8))) . "\n");
    $found = $scan($sandbox);
    unlink($sandbox . '/planted.env');
    rmdir($sandbox);
    fwrite(STDOUT, [] === $found ? "secret-scan self-test: FAILED (planted key not found)\n" : "secret-scan self-test: ok\n");
    exit([] === $found ? 1 : 0);
}

$findings = $scan(dirname(__DIR__));

foreach ($findings as $finding) {
    fwrite(STDERR, $finding . "\n");
}

fwrite(STDOUT, sprintf("secret-scan: %d finding(s)\n", count($findings)));
exit([] === $findings ? 0 : 1);
