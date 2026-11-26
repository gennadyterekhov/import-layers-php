<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Config\ConfigService;
use Gennadyterekhov\ImportLayersPhp\Dto\AnalysisResult;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use InvalidArgumentException;
use SplFileInfo;

final readonly class App
{
    public function __construct(
        private Analyzer $analyzer,
        private bool $graph = false,
    ) {}

    public static function createApp($argc = 0, $argv = []): App
    {
        $paths = [];
        $configPath = null;
        $graph = false;
        for ($index = 1; $index < $argc; $index++) {
            $argument = $argv[$index];
            if ($argument === '--') {
                array_push($paths, ...array_slice($argv, $index + 1));
                break;
            }
            if ($argument === '-c' || $argument === '--config') {
                $configPath = $argv[++$index] ?? '';
                if ($configPath === '' || str_starts_with($configPath, '-')) {
                    throw new InvalidArgumentException("The $argument option requires a path to a config file.");
                }
            } elseif (str_starts_with($argument, '--config=')) {
                $configPath = substr($argument, strlen('--config='));
                if ($configPath === '') {
                    throw new InvalidArgumentException('The --config option requires a path to a config file.');
                }
            } elseif ($argument === '--graph') {
                $graph = true;
            } elseif (str_starts_with($argument, '-')) {
                throw new InvalidArgumentException("Unknown option: $argument");
            } else {
                $paths[] = $argument;
            }
        }

        $config = (new ConfigService())->readConfigFromFileIntoDto(configPath: $configPath, paths: $paths);
        return self::createAppWithConfig($config, $graph);
    }

    public static function createAppWithConfig(Config $config, bool $graph = false): App
    {
        return new App(new Analyzer($config, new UseStmtChecker()), $graph);
    }

    public function analyze(): AnalysisResult
    {
        return $this->analyzer->analyze($this->graph);
    }

    public function analyzeFile(SplFileInfo $fi): AnalysisResult
    {
        return $this->analyzer->analyzeFile($fi);
    }

    public function outputResult(AnalysisResult $result): int
    {
        foreach ($result->errors as $error) {
            fwrite(STDERR, $error . "\n");
        }
        if ($result->problems !== []) {
            return 2;
        }

        if ($this->graph) {
            echo json_encode($result->graph, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . "\n";
        } elseif ($result->violations === []) {
            echo "OK\n";
        }
        return $result->violations === [] ? 0 : 1;
    }
}
