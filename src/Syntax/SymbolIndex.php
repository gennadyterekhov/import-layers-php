<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Syntax;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Use_;

final class SymbolIndex
{
    /** @var array<string, string> */
    private array $pathsBySymbol = [];
    /** @var array<string, list<string>> */
    private array $namespacesByPath = [];

    /** @param list<NamespaceBlock> $blocks */
    public function addFile(string $path, array $blocks): void
    {
        foreach ($blocks as $block) {
            $this->namespacesByPath[$path][] = $block->name;
            $prefix = $block->name === '' ? '' : $block->name . '\\';
            foreach ($block->statements as $statement) {
                if ($statement instanceof ClassLike && $statement->name !== null) {
                    $this->addSymbol(Use_::TYPE_NORMAL, $prefix . $statement->name, $path);
                } elseif ($statement instanceof Function_) {
                    $this->addSymbol(Use_::TYPE_FUNCTION, $prefix . $statement->name, $path);
                } elseif ($statement instanceof Const_) {
                    foreach ($statement->consts as $constant) {
                        $this->addSymbol(Use_::TYPE_CONSTANT, $prefix . $constant->name, $path);
                    }
                }
            }
        }
    }

    public function pathFor(int $kind, string $name): ?string
    {
        return $this->pathsBySymbol[$this->symbolKey($kind, $name)] ?? null;
    }

    /** @return list<string> */
    public function namespacesFor(string $path): array
    {
        return $this->namespacesByPath[$path] ?? [];
    }

    private function addSymbol(int $kind, string $name, string $path): void
    {
        $this->pathsBySymbol[$this->symbolKey($kind, $name)] ??= $path;
    }

    private function symbolKey(int $kind, string $name): string
    {
        // PHP treats the namespace portion of constants as case-insensitive.
        if ($kind === Use_::TYPE_CONSTANT) {
            $separator = strrpos($name, '\\');
            if ($separator !== false) {
                $name = strtolower(substr($name, 0, $separator)) . substr($name, $separator);
            }
        } else {
            $name = strtolower($name);
        }
        return "$kind:$name";
    }
}
