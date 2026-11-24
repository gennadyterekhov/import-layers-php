<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Dto;

final readonly class AnalysisResult
{
    public array $errors;
    public array $violations;

    public function __construct(
        array $errors = [],
        public array $problems = [],
        public array $graph = ['nodes' => [], 'edges' => []],
    ) {
        $this->errors = [...$problems, ...$errors];
        $this->violations = $errors;
    }
}
