<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Config;

use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use Gennadyterekhov\ImportLayersPhp\Project\Project;
use RuntimeException;

final readonly class ConfigService
{
    public function __construct() {}

    public function readConfigFromFileIntoDto(string $dirToCheck = '', ?string $configPath = null): Config
    {
        $data = json_decode($this->getConfigFileContents($configPath), true);
        if ($configPath !== null && !is_array($data)) {
            throw new RuntimeException('Invalid JSON config file: ' . $configPath);
        }

        return new Config(
            $data['debug'] ?? false,
            $data['ignoreTests'] ?? false,
            $data['rootDir'] ?? $dirToCheck,
            $data['layers'] ?? [],
        );
    }

    private function getConfigFileContents(?string $configPath): string
    {
        $path = $configPath ?? Project::getProjectRoot() . '/import_layers.json';
        $contents = $configPath === null ? file_get_contents($path) : @file_get_contents($path);
        if ($contents !== false) {
            return $contents;
        }
        if ($configPath !== null) {
            throw new RuntimeException('Could not read config file: ' . $configPath);
        }

        return '';
    }
}
