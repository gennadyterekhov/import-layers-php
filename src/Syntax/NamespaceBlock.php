<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Syntax;

use PhpParser\Node\Stmt;

final readonly class NamespaceBlock
{
    /** @param list<Stmt> $statements */
    public function __construct(public string $name, public array $statements) {}
}
