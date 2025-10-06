<?php

declare(strict_types=1);

namespace Tests\Unit\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Analyzer\FileAnalyzer;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PHPUnit\Framework\TestCase;
use PhpParser\ParserFactory;

final class AnalyzerTest extends TestCase
{
    public function testCanAnalyze(): void
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $config = new Config();
        $fa = new FileAnalyzer($parser);
        $service = new Analyzer($fa);

        $res = $service->analyze($config);
        self::assertCount(0, $res->errors);
    }
}
