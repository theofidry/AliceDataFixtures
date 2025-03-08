<?php

declare(strict_types=1);

/*
 * This file is part of the humbug/php-scoper package.
 *
 * Copyright (c) 2017 Théo FIDRY <theo.fidry@gmail.com>,
 *                    Pádraic Brady <padraic.brady@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/fixtures',
        __DIR__.'/migrations',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withAutoloadPaths([
        __DIR__.'/vendor/autoload.php',
        __DIR__.'/vendor-bin/rector/vendor/autoload.php',
    ])
    ->withImportNames(removeUnusedImports: true)
    ->withPhpSets(php83: true)
    ->withAttributesSets(
        doctrine: true,
        phpunit: true,
    )
    ->withSkip([
        NewInInitializerRector::class => [
            __DIR__.'/src/Loader/FileResolverLoader.php',
            __DIR__.'/src/Loader/PersisterLoader.php',
            __DIR__.'/src/Loader/PurgerLoader.php',
            __DIR__.'/src/Loader/SimpleLoader.php',
        ],
        ReadOnlyPropertyRector::class => [
            __DIR__.'/fixtures/Bridge/Symfony/Entity/Group.php',
            __DIR__.'/fixtures/Bridge/Symfony/Entity/User.php',
        ],
        RemoveParentCallWithoutParentRector::class => [
            __DIR__.'/fixtures/Bridge/Symfony/SymfonyApp/DoctrineKernelWithInvalidDatabase.php',
            __DIR__.'/tests/Bridge/Symfony/**/*.php',
        ],
        StringableForToStringRector::class => [
            __DIR__.'/src/Persistence/PurgeMode.php',
        ],
    ]);
