<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

final readonly class Debug
{
    public function outputDebugIfSkipped(string $layer, bool $debug = false): void
    {
        if (!$debug) {
            return;
        }
        echo 'layer ' . $layer . ' is not present in config. skipping' . PHP_EOL . PHP_EOL;
    }

    public function outputDebug(string $namespace, string $layer, int $currentLayer, int $layerThatYoureTryingToImport, bool $isOk, bool $debug = false): void
    {
        if (!$debug) {
            return;
        }
        echo 'comparing ' . $namespace . ' and ' . $layer . PHP_EOL;
        echo ' less the number - higher the layer ' . PHP_EOL;
        echo ' ' . $namespace . ' has layer number ' . $currentLayer . PHP_EOL;
        echo ' ' . $layer . ' has layer number ' . $layerThatYoureTryingToImport . PHP_EOL;

        $boolStr = ($isOk) ? 'true' : 'false';
        echo ' ' . $currentLayer . ' >= ' . $layerThatYoureTryingToImport . ' ====> ' . $boolStr . PHP_EOL;
        echo PHP_EOL;
    }
}
