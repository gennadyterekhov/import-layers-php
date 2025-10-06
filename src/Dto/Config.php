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

    public function getLayer(string $namespace): int
    {
        foreach ($this->layers as $index => $layer) {
            if (str_contains($namespace, $layer)) {
                return $index;
            }
        }
        return 0;
    }

    public function isOk(string $namespace, string $layer): bool
    {
        $currentLayer = $this->getLayer($namespace);
        $layerThatYoureTryingToImport = $this->getLayer($layer);

        return $currentLayer >= $layerThatYoureTryingToImport;
    }
}
