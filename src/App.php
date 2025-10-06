<?php declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp;

use Gennadyterekhov\ImportLayersPhp\Config\ConfigService;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

final class App
{
    public function __construct(
        private ConfigService $configService,
    ) {}

    public function do(): void
    {
        // TODO config dirs here
        $inDir = '/some/path';
        $outDir = '/some/other/path';

        $parser = (new ParserFactory())->createForHostVersion();
        $traverser = new NodeTraverser;
        $prettyPrinter = new PrettyPrinter\Standard;

// TODO config visitors here
        $traverser->addVisitor(new NameResolver); // we will need resolved names
// $traverser->addVisitor(new NamespaceConverter); // our own node visitor

// iterate over all .php files in the directory
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($inDir));
        $files = new \RegexIterator($files, '/\.php$/');

        foreach ($files as $file) {
            try {
                // read the file that should be converted
                $code = file_get_contents($file->getPathName());

                // parse
                $stmts = $parser->parse($code);

                // traverse
                $stmts = $traverser->traverse($stmts);

                // pretty print
                $code = $prettyPrinter->prettyPrintFile($stmts);

                // write the converted file to the target directory
                file_put_contents(
                    substr_replace($file->getPathname(), $outDir, 0, strlen($inDir)),
                    $code
                );
            } catch (PhpParser\Error $e) {
                echo 'Parse Error: ', $e->getMessage();
            }
        }

    }

    public function checkImportLayers(): void
    {

        $config = $this->configService->readConfigFromFileIntoDto();

    }

}
