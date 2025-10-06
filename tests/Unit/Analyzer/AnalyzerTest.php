<?php

declare(strict_types=1);

namespace Tests\Unit\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PHPUnit\Framework\TestCase;

final class AnalyzerTest extends TestCase
{
    public function testCanAnalyze(): void
    {
        $config = new Config();
        $service = new Analyzer();
        $res = $service->analyze($config);
        self::assertCount(0, $res->errors);
    }
}
