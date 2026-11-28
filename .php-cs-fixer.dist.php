<?php

declare(strict_types=1);

// PER Coding Style (Spec V4.3.3 §25 pins 3.1). php-cs-fixer 3.95 implements up to 3.0;
// the three points E-046 names — `fn(` without a space, a blank line after
// `namespace`, no hard line limit — are already enforced by the 3.0 rules.
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PER-CS3.0' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/tools']),
    );
