<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Composer\Autoload\ClassLoader;
use Gennadyterekhov\ImportLayersPhp\Dto\AnalysisResult;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass;
use Throwable;

final readonly class Analyzer
{
    public function __construct() {}

    public function analyze(Config $config): AnalysisResult
    {
        $errors = [];
        // TODO config dirs here
        $inDir = $this->getProjectRoot() . '/src';
        $outDir = '/some/other/path';

        $parser = (new ParserFactory())->createForHostVersion();
        $traverser = new NodeTraverser;
        $prettyPrinter = new Standard;

        // TODO config visitors here
        $traverser->addVisitor(new NameResolver); // we will need resolved names
        // $traverser->addVisitor(new NamespaceConverter); // our own node visitor

        // iterate over all .php files in the directory
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($inDir));
        $files = new \RegexIterator($files, '/\.php$/');

        foreach ($files as $file) {
            try {
                var_dump($file->getPathName());

                // read the file that should be converted
                $code = file_get_contents($file->getPathName());

                // parse
                $stmts = $parser->parse($code);

//                var_dump($stmts);

                // traverse
                $stmts = $traverser->traverse($stmts);

//                var_dump($stmts);

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
