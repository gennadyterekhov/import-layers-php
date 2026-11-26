<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Project;

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final readonly class Project
{
    private const EXCLUDED_DIRS = [
        '.git', '.hg', '.svn', '.venv', '.nuxt', '.next', '.cache', '.pytest_cache',
        '__pycache__', 'build', 'dist', 'node_modules', 'vendor', 'venv',
    ];

    public function __construct(public string $root) {}

    public static function getProjectRoot(): string
    {
        $config = self::findDefaultConfig();
        return $config === null ? self::workingDirectory() : dirname($config);
    }

    public static function workingDirectory(): string
    {
        $cwd = getcwd();
        if ($cwd === false) {
            throw new RuntimeException('Could not determine the current working directory');
        }
        return $cwd;
    }

    public static function findDefaultConfig(): ?string
    {
        $directory = self::workingDirectory();
        do {
            foreach (['import_layers.json', 'import_layers.yaml', 'import_layers.yml'] as $name) {
                $path = $directory . DIRECTORY_SEPARATOR . $name;
                if (is_file($path)) {
                    return $path;
                }
            }
            $parent = dirname($directory);
            if ($parent === $directory) {
                return null;
            }
            $directory = $parent;
        } while (true);
    }

    public static function absolutePath(string $path, string $base): string
    {
        if (str_starts_with($path, '/') || preg_match('~^[A-Za-z]:[/\\\\]~', $path) || str_starts_with($path, '\\\\')) {
            return $path;
        }
        return $base . DIRECTORY_SEPARATOR . $path;
    }

    public function relativePath(string $absolutePath): string
    {
        if ($absolutePath === $this->root) {
            return '';
        }

        $prefix = rtrim($this->root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($absolutePath, $prefix)) {
            throw new RuntimeException("Path is outside the project: $absolutePath");
        }
        return str_replace(DIRECTORY_SEPARATOR, '/', substr($absolutePath, strlen($prefix)));
    }

    /** @return array<string, string> Project-relative paths mapped to absolute paths. */
    public function files(array $selection, bool $ignoreTests): array
    {
        $files = [];
        foreach ($selection as $path) {
            $absolutePath = realpath($path);
            if ($absolutePath === false || (!is_dir($absolutePath) && !is_file($absolutePath))) {
                throw new RuntimeException("Invalid project path: $path");
            }
            $relative = $this->relativePath($absolutePath);
            if ($this->isExcluded($relative, $ignoreTests)) {
                continue;
            }

            if (is_file($absolutePath)) {
                if ($this->isSource($relative, $ignoreTests)) {
                    $files[$relative] = $absolutePath;
                }
                continue;
            }

            $entries = new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($absolutePath, RecursiveDirectoryIterator::SKIP_DOTS),
                fn ($entry) => !$entry->isLink() && (
                    !$entry->isDir() || !$this->isExcluded($this->relativePath($entry->getPathname()), $ignoreTests)
                ),
            );
            foreach (new RecursiveIteratorIterator($entries) as $entry) {
                $absolute = $entry->getPathname();
                $relative = $this->relativePath($absolute);
                if ($entry->isFile() && $this->isSource($relative, $ignoreTests)) {
                    $files[$relative] = $absolute;
                }
            }
        }
        ksort($files, SORT_STRING);
        return $files;
    }

    private function isExcluded(string $path, bool $ignoreTests): bool
    {
        $excluded = $ignoreTests ? [...self::EXCLUDED_DIRS, 'test', 'tests', '__tests__'] : self::EXCLUDED_DIRS;
        return array_intersect(explode('/', $path), $excluded) !== [];
    }

    private function isSource(string $path, bool $ignoreTests): bool
    {
        return str_ends_with($path, '.php') && (!$ignoreTests || !preg_match('~(?:Test|_test)\.php$~', $path));
    }
}
