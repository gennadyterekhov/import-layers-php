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
}
