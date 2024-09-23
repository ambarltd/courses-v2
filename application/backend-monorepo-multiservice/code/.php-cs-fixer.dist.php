<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect()) // @TODO 4.0 no need to call this manually
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'general_phpdoc_annotation_remove' => ['annotations' => ['expectedDeprecation']],
        'modernize_strpos' => true,
        'no_useless_concat_operator' => true,
        'numeric_literal_separator' => true,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
        'phpdoc_to_property_type' => true,
        'php_unit_strict' => false,
    ])
    ->setFinder(
        (new Finder())
            ->ignoreDotFiles(true)
            ->ignoreVCSIgnored(true)
            ->filter(
                static function (\SplFileInfo $file): bool {
                    return !str_contains($file->getFilename(), "IntegrationTest.php");
                }
            )
            ->in(__DIR__)
    );