<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/routes',
        __DIR__.'/database/seeders',
    ])
    ->withSkip([
        __DIR__.'/vendor',
        __DIR__.'/storage',
        __DIR__.'/bootstrap',
        __DIR__.'/public',
        __DIR__.'/resources',
        __DIR__.'/config',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_85,
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: false,
        naming: false,
        instanceOf: false,
        earlyReturn: false,
        strictBooleans: false,
    )
    ->withImportNames(removeUnusedImports: true);
