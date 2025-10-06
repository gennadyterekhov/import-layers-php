<?php declare(strict_types=1);

namespace Tests\Helpers;

use Gennadyterekhov\ImportLayersPhp\Project\Project;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

final class Testdata
{
    public static function getTestCode(string $methodName): string
    {
        [$path, $onlyFuncName] = self::getPathAndFuncName($methodName);
        $fullPathToFile = self::getPathToTestdata($path) . "{$onlyFuncName}.php";

        assert(file_exists($fullPathToFile));

        return file_get_contents($fullPathToFile);
    }

    public static function getPathToTestdata(string $pathToTestClass): string
    {
        return str_replace('tests/Helpers', '', __DIR__) . $pathToTestClass;
    }

    public static function getPathAndFuncName(string $methodName): array
    {
        $parts = explode('::', $methodName);
        $path = $parts[0];
        $onlyFuncName = $parts[1];
        $path = str_replace('\\', '/', $path);
        $path = str_replace('Tests/', 'tests/testdata/', $path);

        return [Project::getProjectRoot() . '/' . $path . '/', $onlyFuncName];
    }
}
