<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Config\ConfigService;
use Gennadyterekhov\ImportLayersPhp\Dto\AnalysisResult;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PhpParser\ParserFactory;
use SplFileInfo;

final readonly class App
{
    public function __construct(
        private UseStmtChecker $useStmtChecker,
        private Config $config,
        private Analyzer $analyzer,
    ) {}

    public static function createApp($argc = 0, $argv = ''): App
    {
        $dirToCheck = ($argc > 1) ? $argv[1] : '';
        $config = (new ConfigService())->readConfigFromFileIntoDto($dirToCheck);

        return App::createAppWithConfig($config);
    }

    public static function createAppWithConfig(Config $config): App
    {
        $ds = new Debug();
        $useStmtChecker = new UseStmtChecker($ds);
        $parser = (new ParserFactory())->createForHostVersion();
        $analyzer = new Analyzer($parser, $config, $useStmtChecker);

        return new App(
            $useStmtChecker,
            $config,
            $analyzer,
        );
    }

    public function analyze(): AnalysisResult
    {
        return $this->analyzer->analyze();
    }

    public function analyzeFile(SplFileInfo $fi): AnalysisResult
    {
        return $this->analyzer->analyzeFile($fi);
    }

    public function outputResult(AnalysisResult $result): void
    {
        if (count($result->errors) > 0) {
            foreach ($result->errors as $error) {
                echo $error . PHP_EOL;
            }
        } else {
            echo 'OK' . PHP_EOL;
        }
        echo PHP_EOL;
    }
}
