<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

final readonly class Debug
{
    public function processingFile(string $path, bool $graph): void
    {
        fwrite($graph ? STDERR : STDOUT, "processing file $path\n");
    }
}
