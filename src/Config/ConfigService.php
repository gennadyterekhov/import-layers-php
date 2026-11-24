<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Config;

use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use Gennadyterekhov\ImportLayersPhp\Project\Project;
use JsonException;
use RuntimeException;
use stdClass;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final readonly class ConfigService
{
    public function readConfigFromFileIntoDto(string $dirToCheck = '', ?string $configPath = null, array $paths = []): Config
    {
        $default = Project::findDefaultConfig();
        $cwd = Project::workingDirectory();
        $projectRoot = $default === null ? $cwd : dirname($default);
        $path = $configPath === null ? $default : Project::absolutePath($configPath, $cwd);
        if ($path === null) {
            throw new RuntimeException('Could not find import_layers.json, import_layers.yaml or import_layers.yml');
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Could not read config file: $path");
        }

        try {
            $data = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['yaml', 'yml'], true)
                ? Yaml::parse($contents, Yaml::PARSE_OBJECT_FOR_MAP | Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE)
                : json_decode($contents, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException | ParseException $exception) {
            throw new RuntimeException("$path: " . $exception->getMessage(), previous: $exception);
        }
        if (!$data instanceof stdClass) {
            throw new RuntimeException("$path: expected a configuration object");
        }
        $data = (array) $data;

        $layers = $data['layers'] ?? null;
        if (!is_array($layers) || !array_is_list($layers) || $layers === []) {
            throw new RuntimeException("$path: layers must be a non-empty list of unique strings");
        }
        foreach ($layers as $layer) {
            if (!is_string($layer) || trim($layer) === '') {
                throw new RuntimeException("$path: layers must be a non-empty list of unique strings");
            }
        }
        if (count(array_unique($layers)) !== count($layers)) {
            throw new RuntimeException("$path: layers must be a non-empty list of unique strings");
        }

        foreach (['debug', 'ignoreTests', 'ignore_tests'] as $option) {
            if (array_key_exists($option, $data) && !is_bool($data[$option])) {
                throw new RuntimeException("$path: $option must be a boolean");
            }
        }
        if (array_key_exists('rootDir', $data) && !is_string($data['rootDir'])) {
            throw new RuntimeException("$path: rootDir must be a directory path");
        }
        if ($paths === [] && $dirToCheck !== '') {
            $paths = [$dirToCheck];
        }

        return new Config(
            debug: $data['debug'] ?? false,
            ignoreTests: $data['ignoreTests'] ?? $data['ignore_tests'] ?? false,
            dirToCheck: $data['rootDir'] ?? '',
            layers: $layers,
            paths: array_map(fn (string $selection) => Project::absolutePath($selection, $cwd), $paths),
            projectRoot: $projectRoot,
        );
    }
}
