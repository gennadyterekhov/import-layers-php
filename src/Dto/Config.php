<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Dto;

final readonly class Config
{
    private array $layerNameToNumber;

    public function __construct(
        public bool $debug = false,
        public bool $ignoreTests = false,
        public array $layers = [],
    )
    {
        $this->layerNameToNumber = $this->createMap();
    }

    private function createMap(): array
    {
        $map = [];
        $total = count($this->layers);
        for ($i = 0; $i < $total; $i++) {
            $layerName = $this->layers[$i];
            $map[$layerName] = $i;
        }

        return $map;
    }

    public function getLayer(string $namespace): int
    {
        if (!array_key_exists($namespace, $this->layerNameToNumber)) {
            return 0;
        }
        return $this->layerNameToNumber[$namespace];
    }

    public function isOk(string $namespace, string $layerThatYoureTryingToImport): bool
    {
        $currentLayer = $this->getLayer($namespace);
        $layerThatYoureTryingToImport = $this->getLayer($layerThatYoureTryingToImport);
        return $currentLayer <= $layerThatYoureTryingToImport;
    }
}
