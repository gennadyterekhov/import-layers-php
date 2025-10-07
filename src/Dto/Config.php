<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Dto;

final readonly class Config
{
    public function __construct(
        public bool $debug = false,
        public bool $ignoreTests = false,
        public array $layers = [],
    ) {}

    public function getLayer(string $namespace, bool $isDestination = true): int
    {
        foreach ($this->layers as $index => $layer) {
            if (str_contains($namespace, $layer)) {
                return $index;
            }
        }
        if ($isDestination) {
            return -1;
        }
        return 1000;
    }

    public function isOk(string $namespace, string $layer): bool
    {
        $currentLayer = $this->getLayer($namespace, false);
        $layerThatYoureTryingToImport = $this->getLayer($layer);

        if ($layerThatYoureTryingToImport === -1) {
            if ($this->debug) {
                echo 'layer ' . $layer . ' is not present in config. skipping' . PHP_EOL . PHP_EOL;
            }
            return true;
        }

        if ($this->debug) {
            echo 'comparing ' . $namespace . ' and ' . $layer . PHP_EOL;
            echo ' less the number - higher the layer ' . PHP_EOL;
            echo $namespace . ' has layer number ' . $currentLayer . PHP_EOL;
            echo $layer . ' has layer number ' . $layerThatYoureTryingToImport . PHP_EOL;

            $boolStr = ($currentLayer >= $layerThatYoureTryingToImport) ? 'true' : 'false';
            echo $currentLayer . ' >= ' . $layerThatYoureTryingToImport . ' ====> ' . $boolStr . PHP_EOL;
            echo PHP_EOL;
        }

        return $currentLayer >= $layerThatYoureTryingToImport;
    }
}
