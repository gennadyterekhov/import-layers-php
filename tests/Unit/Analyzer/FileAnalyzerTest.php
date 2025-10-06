<?php

declare(strict_types=1);

namespace Tests\Unit\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Analyzer\FileAnalyzer;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PHPUnit\Framework\TestCase;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use SplFileInfo;
use Tests\Helpers\Testdata;

final class FileAnalyzerTest extends TestCase
{
    public function testCanAnalyzeFile(): void
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $traverser = new NodeTraverser;
        $config = new Config();
        $fa = new FileAnalyzer($parser, $traverser);
        [$path, $onlyFuncName] = Testdata::getPathAndFuncName(__METHOD__);

        var_dump($path);
        $path = $path . 'LowUsesHigh/Low.php';
        $fileInfo = new SplFileInfo($path);
        $res = $fa->analyzeFile($config, $fileInfo);
        self::assertCount(0, $res->errors);
    }

    public function testHighCannotUseLow(): void
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $traverser = new NodeTraverser;
        $config = new Config();
        $fa = new FileAnalyzer($parser, $traverser);
        [$path, $onlyFuncName] = Testdata::getPathAndFuncName(__METHOD__);

        var_dump($path);
        $path = $path . 'HighUsesLow/High.php';
        $fileInfo = new SplFileInfo($path);
        $res = $fa->analyzeFile($config, $fileInfo);
        self::assertCount(1, $res->errors);
        self::assertEquals('Cannot use Low from High', $res->errors[0]);
    }
}
