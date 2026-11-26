<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Syntax\NamespaceBlock;
use Gennadyterekhov\ImportLayersPhp\Syntax\SymbolIndex;

final readonly class ImportExtractor
{
    public function __construct(private SymbolIndex $symbols, private IncludeResolver $includes) {}

    /** @return list<ImportReference> */
    public function extract(NamespaceBlock $block, string $sourcePath): array
    {
        $imports = [];
        foreach ($block->statements as $statement) {
            if (!in_array($statement['nodeType'], ['Stmt_Use', 'Stmt_GroupUse'], true)) {
                continue;
            }

            $prefix = $statement['nodeType'] === 'Stmt_GroupUse' ? $statement['prefix']['name'] . '\\' : '';
            foreach ($statement['uses'] as $item) {
                $name = $prefix . $item['name']['name'];
                $path = $this->symbols->pathFor($item['type'] ?: $statement['type'], $name);
                $imports[] = new ImportReference($name, $item['attributes']['startLine'], $path, [$name]);
            }
        }
        foreach ($this->includes->imports($block->statements, $sourcePath) as [$path, $line]) {
            $imports[] = new ImportReference($path, $line, $path, $this->symbols->namespacesFor($path));
        }

        usort($imports, fn (ImportReference $first, ImportReference $second) => $first->line <=> $second->line);
        return $imports;
    }
}
