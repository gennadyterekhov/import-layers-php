<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Dto\AnalysisResult;
use Gennadyterekhov\ImportLayersPhp\Dto\Config;
use Gennadyterekhov\ImportLayersPhp\Project\Project;
use Gennadyterekhov\ImportLayersPhp\Syntax\NamespaceBlock;
use Gennadyterekhov\ImportLayersPhp\Syntax\SourceParser;
use Gennadyterekhov\ImportLayersPhp\Syntax\SymbolIndex;
use PhpParser\Error;
use PhpParser\Parser;
use RuntimeException;
use SplFileInfo;

final readonly class Analyzer
{
    private Project $project;
    private SourceParser $sourceParser;

    public function __construct(
        Parser $parser,
        private Config $config,
        private UseStmtChecker $useStmtChecker,
        private Debug $debug = new Debug(),
    ) {
        $this->project = new Project($config->projectRoot ?? Project::getProjectRoot());
        $this->sourceParser = new SourceParser($parser);
    }

    public function analyze(bool $graph = false): AnalysisResult
    {
        $selection = $this->config->paths ?: [$this->project->root];
        if ($this->config->dirToCheck !== '') {
            $directory = Project::absolutePath($this->config->dirToCheck, $this->project->root);
            if (!is_dir($directory)) {
                throw new RuntimeException("Invalid project directory: $directory");
            }
            $selection = [$directory];
        }
        $selected = $this->project->files($selection, $this->config->ignoreTests);
        $files = $selection === [$this->project->root]
            ? $selected
            : $this->project->files([$this->project->root], $this->config->ignoreTests);
        return $this->analyzeFiles($selected, $files, $graph);
    }

    public function analyzeFile(SplFileInfo $fileInfo): AnalysisResult
    {
        $path = $fileInfo->getRealPath();
        if ($path === false || !is_file($path)) {
            return new AnalysisResult(problems: ['Invalid file: ' . $fileInfo->getPathname()]);
        }
        $files = $this->project->files([$this->project->root], $this->config->ignoreTests);
        return $this->analyzeFiles([$this->project->relativePath($path) => $path], $files, false);
    }

    /**
     * @param array<string, string> $selected
     * @param array<string, string> $files
     */
    private function analyzeFiles(array $selected, array $files, bool $graph): AnalysisResult
    {
        $files += $selected;
        $symbols = new SymbolIndex();

        [$parsed, $problems] = $this->indexSources($files, $selected, $symbols, $graph);
        $includes = new IncludeResolver($this->project->root, array_flip($files));
        $extractor = new ImportExtractor($symbols, $includes);

        $references = [];
        foreach ($parsed as $path => $blocks) {
            foreach ($blocks as $block) {
                $sourceLayer = $this->useStmtChecker->getLayerNumber($this->config, $block->name, $path);
                foreach ($extractor->extract($block, $files[$path]) as $import) {
                    $targetLayer = $this->useStmtChecker->getLayerNumber(
                        $this->config,
                        $import->targetNames,
                        $import->targetPath,
                    );
                    $references[] = [
                        'from' => $path,
                        'to' => $import->targetPath ?? '',
                        'specifier' => $import->name,
                        'line' => $import->line,
                        'fromLayer' => $sourceLayer === null ? '' : $this->config->layers[$sourceLayer],
                        'toLayer' => $targetLayer === null ? '' : $this->config->layers[$targetLayer],
                        'resolvedLayers' => true,
                    ];
                }
            }
        }

        $checked = Pipeline::check([
            'language' => 'php',
            'interpreter' => PHP_BINARY,
            'root' => $this->project->root,
            'layers' => $this->config->layers,
            'files' => array_keys($parsed),
            'references' => $references,
        ]);
        if (!$graph) {
            return new AnalysisResult($checked['violations'], $problems);
        }

        $nodes = array_unique([...array_keys($selected), ...array_column($checked['edges'], 'to')]);
        sort($nodes, SORT_STRING);
        return new AnalysisResult($checked['violations'], $problems, [
            'nodes' => $nodes,
            'edges' => $checked['edges'],
        ]);
    }

    /**
     * @param array<string, string> $files
     * @param array<string, string> $selected
     * @return array{array<string, list<NamespaceBlock>>, list<string>}
     */
    private function indexSources(array $files, array $selected, SymbolIndex $symbols, bool $graph): array
    {
        $parsed = [];
        $problems = [];
        foreach ($files as $relative => $path) {
            $checking = isset($selected[$relative]);
            if ($checking && $this->config->debug) {
                $this->debug->processingFile($relative, $graph);
            }

            $code = @file_get_contents($path);
            if ($code === false) {
                if ($checking) {
                    $problems[] = "$relative: could not read source file";
                }
                continue;
            }

            try {
                $blocks = $this->sourceParser->parse($code);
            } catch (Error $error) {
                if ($checking) {
                    $problems[] = sprintf('%s:%d: %s', $relative, $error->getStartLine(), $error->getRawMessage());
                }
                continue;
            } catch (RuntimeException $error) {
                if ($checking) {
                    $problems[] = "$relative: " . $error->getMessage();
                }
                continue;
            }
            $symbols->addFile($relative, $blocks);
            if ($checking) {
                $parsed[$relative] = $blocks;
            }
        }
        return [$parsed, $problems];
    }

}
