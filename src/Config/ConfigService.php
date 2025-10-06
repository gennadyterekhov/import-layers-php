<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Config;

use Composer\Autoload\ClassLoader;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use ReflectionClass;

final readonly class ConfigService
{
    public function __construct() {}

    public function readConfigFromFileIntoDto(): Config
    {
        $data = json_decode($this->getConfigFileContents(), true);

        return new Config(
            $data['debug'] ?? false,
            $data['ignoreTests'] ?? false,
            $data['layers'] ?? [],
        );
    }

    private function getConfigFileContents(): string
    {
        $path = $this->getProjectRoot() . '/import_layers.json';
        $contents = file_get_contents($path);
        if ($contents === false) {
            return '';
        }
        return $contents;
    }

    private function getProjectRoot(): string
    {
        $reflection = new ReflectionClass(ClassLoader::class);
        $vendorDir = dirname($reflection->getFileName(), 2);

        return dirname($vendorDir);
    }
}
