<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Composer\Autoload\ClassLoader;
use Gennadyterekhov\ImportLayersPhp\Dto\AnalysisResult;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\TextUI\Configuration\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use Throwable;

final readonly class Analyzer
{
    public function __construct(
        private NodeTraverser $traverser,
        private FileAnalyzer $fileAnalyzer,
    ) {}

    public function analyze(Config $config): AnalysisResult
    {
        $errors = [];
        // TODO config dirs here
        $inDir = $this->getProjectRoot() . '/src';
        $outDir = '/some/other/path';

        // TODO config visitors here
        $this->traverser->addVisitor(new NameResolver); // we will need resolved names
        // $traverser->addVisitor(new NamespaceConverter); // our own node visitor

        // iterate over all .php files in the directory
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

        $analysis = new AnalysisResult($errors);
        return $analysis;
    }

    private function getProjectRoot(): string
    {
        $reflection = new ReflectionClass(ClassLoader::class);
        $vendorDir = dirname($reflection->getFileName(), 2);

        return dirname($vendorDir);
    }
}
