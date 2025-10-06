<?php declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp;

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Config\ConfigService;

final class App
{
    public function __construct(
        private ConfigService $configService,
        private Analyzer $analyzer,
    ) {}

    public function checkImportLayers(): void
    {
        $config = $this->configService->readConfigFromFileIntoDto();

        $this->analyzer->analyze($config);
    }
}
