<?php

declare(strict_types=1);

namespace Tests\Unit\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Analyzer\App;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PHPUnit\Framework\TestCase;
use PhpParser\ParserFactory;
use SplFileInfo;
use Tests\Helpers\Testdata;

final class AnalyzerTest extends TestCase
{
    public function testCanAnalyzeAll(): void
    {
        $app = App::createApp();
        $res = $app->analyze();
        var_dump($res);
        self::assertCount(0, $res->errors);
    }

    public function testCanUseCustomConfigWithPositionalDirectory(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'import-layers-');
        self::assertNotFalse($path);

        try {
            file_put_contents($path, <<<'JSON'
                {
                    "layers": [
                        "HighUsesLow\\High", "HighUsesLow\\Low"
                    ]
                }
                JSON);
            $directory = 'tests/testdata/Unit/Analyzer/AnalyzerTest/HighUsesLow';

            foreach ([['-c', $path, $directory], [$directory, '-c', $path]] as $arguments) {
                $app = App::createApp(4, ['import-layers.php', ...$arguments]);
                $result = $app->analyze();

                self::assertCount(1, $result->errors);
                self::assertStringContainsString('HighUsesLow\\Low\\Low from', $result->errors[0]);
            }
        } finally {
            unlink($path);
        }
    }

    public function testCanAnalyzeFile(): void
    {
        $app = App::createAppWithConfig(new Config(layers: ['HighUsesLow\High', 'HighUsesLow\Low']));

        [$path, $onlyFuncName] = Testdata::getPathAndFuncName(__METHOD__);

        $path = $path . 'LowUsesHigh/Low/Low.php';
        $fileInfo = new SplFileInfo($path);
        $res = $app->analyzeFile($fileInfo);
        var_dump($res);
        self::assertCount(0, $res->errors);
    }

    public function testHighCannotUseLow(): void
    {
        $app = App::createAppWithConfig(new Config(layers: ['HighUsesLow\High', 'HighUsesLow\Low']));

        [$path, $onlyFuncName] = Testdata::getPathAndFuncName(__METHOD__);

        $path = $path . 'HighUsesLow/High/High.php';
        $fileInfo = new SplFileInfo($path);
        $res = $app->analyzeFile($fileInfo);
        self::assertCount(1, $res->errors);
        self::assertStringStartsWith('Cannot import', $res->errors[0]);
    }
}
