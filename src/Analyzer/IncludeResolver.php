<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Project\Project;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;

final readonly class IncludeResolver
{
    /** @param array<string, string> $relativeByAbsolute */
    public function __construct(private string $projectRoot, private array $relativeByAbsolute) {}

    /** @return iterable<array{string, int}> */
    public function imports(array $statements, string $source): iterable
    {
        $finder = new NodeFinder();
        foreach ($finder->findInstanceOf($statements, Include_::class) as $include) {
            $literal = $this->staticPath($include->expr, $source);
            if ($literal === null || $literal === '' || str_contains($literal, chr(0))) {
                continue;
            }

            foreach ([dirname($source), $this->projectRoot] as $directory) {
                $absolute = realpath(Project::absolutePath($literal, $directory));
                if ($absolute !== false && isset($this->relativeByAbsolute[$absolute])) {
                    yield [$this->relativeByAbsolute[$absolute], $include->getStartLine()];
                    break;
                }
            }
        }
    }

    private function staticPath(Expr $expression, string $source): ?string
    {
        if ($expression instanceof String_) {
            return $expression->value;
        }
        if ($expression instanceof Dir) {
            return dirname($source);
        }
        if ($expression instanceof File) {
            return $source;
        }
        if ($expression instanceof Concat) {
            $left = $this->staticPath($expression->left, $source);
            $right = $this->staticPath($expression->right, $source);
            if ($left !== null && $right !== null) {
                return $left . $right;
            }
        }
        return null;
    }
}
