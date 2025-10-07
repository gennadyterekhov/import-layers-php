<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use CallbackFilterIterator;
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
        $inDir = Project::getProjectRoot();

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($inDir));
        $files = new RegexIterator($files, '/\.php$/');
        $files = new CallbackFilterIterator($files, function ($current) {
            return !str_contains($current->getPathname(), DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);
        });

        foreach ($files as $file) {
            try {
                $fileAnalysis = $this->analyzeFile($file);
                $errors = [...$errors, ...$fileAnalysis->errors];
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return new AnalysisResult($errors);
    }

    public function analyzeFile(SplFileInfo $fileInfo): AnalysisResult
    {
        $errors = [];

        try {
            if ($this->config->debug) {
                echo 'processing file ' . $fileInfo->getPathName();
                echo PHP_EOL;
            }

            $code = file_get_contents($fileInfo->getPathName());
            $stmts = $this->parser->parse($code);

            if ($stmts === null) {
                throw new Exception('could not parse file ' . $fileInfo->getPathname());
            }

            foreach ($stmts as $stmt) {
                if ($stmt::class === Namespace_::class) {
                    foreach ($stmt->stmts as $item) {
                        if ($item::class === Use_::class) {
                            $this->analyzeUse($stmt->name->name, $item);
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
    private function analyzeUse(string $namespace, Use_ $useClause): void
    {
        foreach ($useClause->uses as $useItem) {
            if (!$this->config->isOk($namespace, $useItem->name->name)) {
                throw new Exception('Cannot import ' . $useItem->name->name . ' from ' . $namespace);
            }
        }
    }
}
