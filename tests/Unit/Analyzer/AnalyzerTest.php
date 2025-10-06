<?php

declare(strict_types=1);

namespace Tests\Unit\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Config\ConfigService;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PHPUnit\Framework\TestCase;

final class AnalyzerTest extends TestCase
{
    public function testCanAnalyze(): void
    {
        $config = new Config();
        $service = new Analyzer();
        $config = $service->analyze($config);
        self::assertEquals(false, $config->debug);
        self::assertEquals(false, $config->ignoreTests);
        self::assertEquals('src/Dto', $config->layers[0]);
    }
}
