<?php

declare(strict_types=1);

namespace Tests\Unit\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Analyzer\FileAnalyzer;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PHPUnit\Framework\TestCase;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;

final class AnalyzerTest extends TestCase
{
    public function testCanAnalyze(): void
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $traverser = new NodeTraverser;
        $config = new Config();
        $fa = new FileAnalyzer($parser, $traverser);
        $service = new Analyzer($traverser, $fa);

        $res = $service->analyze($config);
        self::assertCount(0, $res->errors);
    }
}
