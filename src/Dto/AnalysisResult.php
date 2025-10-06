<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Dto;

final readonly class AnalysisResult
{
    public function __construct(
        public array $errors = [],
    ) {}
}