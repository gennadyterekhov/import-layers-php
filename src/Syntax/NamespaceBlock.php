<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Syntax;

final readonly class NamespaceBlock
{
    /** @param list<array<string, mixed>> $statements */
    public function __construct(public string $name, public array $statements) {}
}
