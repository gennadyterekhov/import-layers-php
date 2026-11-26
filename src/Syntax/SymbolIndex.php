<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Syntax;

final class SymbolIndex
{
    private const TYPE_NORMAL = 1;
    private const TYPE_FUNCTION = 2;
    private const TYPE_CONSTANT = 3;

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
                $kind = $statement['nodeType'];
                if (in_array($kind, ['Stmt_Class', 'Stmt_Interface', 'Stmt_Trait', 'Stmt_Enum'], true)
                    && isset($statement['name']['name'])) {
                    $this->addSymbol(self::TYPE_NORMAL, $prefix . $statement['name']['name'], $path);
                } elseif ($kind === 'Stmt_Function') {
                    $this->addSymbol(self::TYPE_FUNCTION, $prefix . $statement['name']['name'], $path);
                } elseif ($kind === 'Stmt_Const') {
                    foreach ($statement['consts'] as $constant) {
                        $this->addSymbol(self::TYPE_CONSTANT, $prefix . $constant['name']['name'], $path);
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
        if ($kind === self::TYPE_CONSTANT) {
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
