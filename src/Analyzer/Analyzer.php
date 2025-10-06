<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Dto\AnalysisResult;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use Gennadyterekhov\ImportLayersPhp\Project\Project;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Throwable;

final readonly class Analyzer
{
    public function __construct(
        private FileAnalyzer $fileAnalyzer,
    ) {}

    public function analyze(Config $config): AnalysisResult
    {
        $errors = [];
        $inDir = Project::getProjectRoot() . '/src';

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($inDir));
        $files = new RegexIterator($files, '/\.php$/');

        foreach ($files as $file) {
            try {
                $this->fileAnalyzer->analyzeFile($config, $file);
            } catch (Throwable $exception) {
                echo 'Parse Error: ', $exception->getMessage();
                $errors[] = $exception->getMessage();
            }
        }

        return new AnalysisResult($errors);
    }
}
