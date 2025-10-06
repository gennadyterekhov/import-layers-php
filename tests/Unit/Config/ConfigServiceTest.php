<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Gennadyterekhov\ImportLayersPhp\Config\ConfigService;
use PHPUnit\Framework\TestCase;

final class ConfigServiceTest extends TestCase
{
    public function testCanReadConfigFromFileIntoDto(): void
    {
        $service = new ConfigService();
        $config = $service->readConfigFromFileIntoDto();
        self::assertEquals(false, $config->debug);
        self::assertEquals(false, $config->ignoreTests);
        self::assertEquals('src/Dto', $config->layers[0]);
    }
}
