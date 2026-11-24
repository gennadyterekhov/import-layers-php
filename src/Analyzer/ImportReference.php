<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

final readonly class ImportReference
{
    /** @param list<string> $targetNames */
    public function __construct(
        public string $name,
        public int $line,
        public ?string $targetPath,
        public array $targetNames,
    ) {}
}
