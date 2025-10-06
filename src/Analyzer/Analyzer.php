<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Dto\AnalysisResult;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use Gennadyterekhov\ImportLayersPhp\Project\Project;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Parser;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use Throwable;
use Exception;

final readonly class Analyzer
{
    public function __construct(
        private Parser $parser,

        private Config $config,
    ) {}

    public function analyze(): AnalysisResult
    {
        $errors = [];
        $inDir = Project::getProjectRoot() . '/src';

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($inDir));
        $files = new RegexIterator($files, '/\.php$/');

        foreach ($files as $file) {
            try {
                $this->analyzeFile($this->config, $file);
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return new AnalysisResult($errors);
    }

    public function analyzeFile(Config $config, SplFileInfo $fileInfo): AnalysisResult
    {
        $errors = [];
        try {
            $code = file_get_contents($fileInfo->getPathName());

            $stmts = $this->parser->parse($code);
            if ($stmts === null) {
                throw new Exception('could not parse file ' . $fileInfo->getPathname());
            }

            foreach ($stmts as $stmt) {
                if ($stmt::class === Namespace_::class) {
                    foreach ($stmt->stmts as $item) {
                        if ($item::class === Use_::class) {
                            $this->analyzeUse($config, $stmt->name->name, $item);
                        }
                    }
                }
            }
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }

        return new AnalysisResult($errors);
    }

    /**
     * @throws Exception
     */
    private function analyzeUse(Config $config, string $namespace, Use_ $useClause): void
    {
        foreach ($useClause->uses as $useItem) {
            $currentLayer = $this->getLayer($config, $namespace);
            $layerThatYoureTryingToImport = $this->getLayer($config, $useItem->name->name);
            if ($currentLayer > $layerThatYoureTryingToImport) {
                throw new Exception('Cannot import ' . $useItem->name->name . ' from ' . $namespace);
            }
        }
    }

    private function getLayer(Config $config, string $namespace): int
    {
        $total = count($config->layers);
        for ($i = 0; $i < $total; $i++) {
            $layerName = $config->layers[$i];
            if (str_contains($namespace, $layerName)) {
                return $total - $i;
            }
        }

        return 0;
    }
}
