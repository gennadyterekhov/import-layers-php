<?php

declare(strict_types=1);

namespace Tests\Unit\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PHPUnit\Framework\TestCase;
use PhpParser\ParserFactory;
use SplFileInfo;
use Tests\Helpers\Testdata;

final class AnalyzerTest extends TestCase
{
    public function testCanAnalyzeAll(): void
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $config = new Config();
        $service = new Analyzer($parser, $config);

        $res = $service->analyze();
        self::assertCount(0, $res->errors);
    }

    public function testCanAnalyzeFile(): void
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $config = new Config(layers: ['HighUsesLow\High', 'HighUsesLow\Low']);
        $service = new Analyzer($parser, $config);

        [$path, $onlyFuncName] = Testdata::getPathAndFuncName(__METHOD__);

        $path = $path . 'LowUsesHigh/Low/Low.php';
        $fileInfo = new SplFileInfo($path);
        $res = $service->analyzeFile($fileInfo);
        var_dump($res);
        self::assertCount(0, $res->errors);
    }

    public function testHighCannotUseLow(): void
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $config = new Config(layers: ['HighUsesLow\High', 'HighUsesLow\Low']);
        $service = new Analyzer($parser, $config);

        [$path, $onlyFuncName] = Testdata::getPathAndFuncName(__METHOD__);

        $path = $path . 'HighUsesLow/High/High.php';
        $fileInfo = new SplFileInfo($path);
        $res = $service->analyzeFile($fileInfo);
        self::assertCount(1, $res->errors);
        self::assertStringStartsWith('Cannot import', $res->errors[0]);
    }
}
