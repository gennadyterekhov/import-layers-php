<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Syntax;

final readonly class NamespaceBlocks
{
    /** @return list<NamespaceBlock> */
    public function fromStatements(array $statements): array
    {
        $global = [];
        $blocks = [];
        foreach ($statements as $statement) {
            if ($statement['nodeType'] === 'Stmt_Namespace') {
                $blocks[] = new NamespaceBlock($statement['name']['name'] ?? '', $statement['stmts'] ?? []);
            } else {
                $global[] = $statement;
            }
        }
        if ($global !== []) {
            array_unshift($blocks, new NamespaceBlock('', $global));
        }
        return $blocks;
    }
}
