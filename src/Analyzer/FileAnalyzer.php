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
use ReflectionClass;
use SplFileInfo;
use Throwable;

final readonly class FileAnalyzer
{
    public function __construct(
        private Parser $parser,
        private NodeTraverser $traverser,
    ) {}

    public function analyzeFile(Config $config, SplFileInfo $fileInfo): void
    {
        try {
            var_dump($fileInfo->getPathName());

            // read the file that should be converted
            $code = file_get_contents($fileInfo->getPathName());

            // parse
            $stmts = $this->parser->parse($code);

            var_dump($stmts);

            // traverse
            $stmts = $this->traverser->traverse($stmts);

//                var_dump($stmts);

        } catch (Throwable $exception) {
            echo 'Parse Error: ', $exception->getMessage();
            $errors[] = $exception->getMessage();
        }
    }

    private function getProjectRoot(): string
    {
        $reflection = new ReflectionClass(ClassLoader::class);
        $vendorDir = dirname($reflection->getFileName(), 2);

        return dirname($vendorDir);
    }
}
