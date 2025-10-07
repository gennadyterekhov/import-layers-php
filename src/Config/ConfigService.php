<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Config;

use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use Gennadyterekhov\ImportLayersPhp\Project\Project;

final readonly class ConfigService
{
    public function __construct() {}

    public function readConfigFromFileIntoDto(string $dirToCheck = ''): Config
    {
        $data = json_decode($this->getConfigFileContents(), true);

        return new Config(
            $data['debug'] ?? false,
            $data['ignoreTests'] ?? false,
            $data['rootDir'] ?? $dirToCheck,
            $data['layers'] ?? [],
        );
    }

    private function getConfigFileContents(): string
    {
        $path = Project::getProjectRoot() . '/import_layers.json';
        $contents = file_get_contents($path);
        if ($contents === false) {
            return '';
        }
        return $contents;
    }
}
