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
