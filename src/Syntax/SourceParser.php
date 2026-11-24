<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Syntax;

use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;

final readonly class SourceParser
{
    public function __construct(private Parser $parser) {}

    /** @return list<NamespaceBlock> */
    public function parse(string $code): array
    {
        $global = [];
        $blocks = [];
        foreach ($this->parser->parse($code) ?? [] as $statement) {
            if ($statement instanceof Namespace_) {
                $blocks[] = new NamespaceBlock($statement->name?->toString() ?? '', $statement->stmts ?? []);
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
