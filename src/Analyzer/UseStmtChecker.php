<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Dto\Config;

final readonly class UseStmtChecker
{
    public function __construct(
        private Debug $debugService,
    ) {}

    public function isOk(Config $config, string $namespaceCurrentlyBeingChecked, string $thingThatYouWantToImport, bool $debug = false): bool
    {
        $currentLayer = $this->getLayerNumber($config, $namespaceCurrentlyBeingChecked, false);
        $layerThatYoureTryingToImport = $this->getLayerNumber($config, $thingThatYouWantToImport);

        return $this->isLayerOk($currentLayer, $layerThatYoureTryingToImport, $namespaceCurrentlyBeingChecked, $thingThatYouWantToImport, $debug);
    }

    /**
     * get order of the layer from config. partial matching works so that config isnt forced to use full class names
     * if currently analyzed file is not found in config, assume it as lowest level => dont care about things not specified in config
     */
    private function getLayerNumber(Config $config, string $useStatement, bool $isDestination = true): int
    {
        foreach ($config->layers as $index => $layer) {
            if (str_contains($useStatement, $layer)) {
                return $index;
            }
        }
        if ($isDestination) {
            return -1;
        }
        return 1000;
    }

    /**
     * can import from higher layer. higher layer = closer to the bottom of config file
     */
    private function isLayerOk(int $currentLayer, int $layerThatYoureTryingToImport, string $namespaceCurrentlyBeingChecked, string $layer, bool $debug = false): bool
    {
        if ($layerThatYoureTryingToImport === -1) {
            $this->debugService->outputDebugIfSkipped($layer, $debug);
            return true;
        }
        $this->debugService->outputDebug($namespaceCurrentlyBeingChecked, $layer, $currentLayer, $layerThatYoureTryingToImport, $currentLayer >= $layerThatYoureTryingToImport, $debug);

        return $currentLayer >= $layerThatYoureTryingToImport;
    }
}
