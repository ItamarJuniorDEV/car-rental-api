<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withPhpSets(php83: true)
    ->withCodeQualityLevel(1)
    ->withDeadCodeLevel(1)
    ->withTypeCoverageLevel(1);
