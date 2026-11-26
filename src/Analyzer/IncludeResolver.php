<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Project\Project;

final readonly class IncludeResolver
{
    /** @param array<string, string> $relativeByAbsolute */
    public function __construct(private string $projectRoot, private array $relativeByAbsolute) {}

    /** @return iterable<array{string, int}> */
    public function imports(array $statements, string $source): iterable
    {
        foreach ($this->includesIn($statements) as $include) {
            $literal = $this->staticPath($include['expr'], $source);
            if ($literal === null || $literal === '' || str_contains($literal, chr(0))) {
                continue;
            }

            foreach ([dirname($source), $this->projectRoot] as $directory) {
                $absolute = realpath(Project::absolutePath($literal, $directory));
                if ($absolute !== false && isset($this->relativeByAbsolute[$absolute])) {
                    yield [$this->relativeByAbsolute[$absolute], $include['attributes']['startLine']];
                    break;
                }
            }
        }
    }

    /** @return iterable<array<string, mixed>> */
    private function includesIn(array $node): iterable
    {
        if (($node['nodeType'] ?? null) === 'Expr_Include') {
            yield $node;
        }
        foreach ($node as $key => $child) {
            if ($key !== 'attributes' && is_array($child)) {
                yield from $this->includesIn($child);
            }
        }
    }

    private function staticPath(array $expression, string $source): ?string
    {
        return match ($expression['nodeType']) {
            'Scalar_String' => $expression['value'],
            'Scalar_MagicConst_Dir' => dirname($source),
            'Scalar_MagicConst_File' => $source,
            'Expr_BinaryOp_Concat' => $this->concatenate($expression, $source),
            default => null,
        };
    }

    private function concatenate(array $expression, string $source): ?string
    {
        $left = $this->staticPath($expression['left'], $source);
        $right = $this->staticPath($expression['right'], $source);
        return $left !== null && $right !== null ? $left . $right : null;
    }
}
