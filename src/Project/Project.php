<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Project;

use Composer\Autoload\ClassLoader;
use ReflectionClass;

final readonly class Project
{
    public static function getProjectRoot(): string
    {
        $reflection = new ReflectionClass(ClassLoader::class);
        $vendorDir = dirname($reflection->getFileName(), 2);

        return dirname($vendorDir);
    }
}