<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use Gennadyterekhov\ImportLayersPhp\Dto\Config;

final readonly class UseStmtChecker
{
    public function getLayerNumber(Config $config, string|array $symbols, ?string $path = null): ?int
    {
        $directory = $path === null ? '' : dirname($path);
        foreach ($config->layers as $index => $layer) {
            if (!str_contains($layer, '/')) {
                continue;
            }

            $layer = trim($layer, '/');
            if ($directory === $layer || str_starts_with($directory, "$layer/")) {
                return $index;
            }
        }

        $namespaces = array_map(
            fn (string $symbol) => '\\' . trim(strtolower($symbol), '\\') . '\\',
            (array) $symbols,
        );
        $directories = $path !== null ? explode('/', $directory) : [];
        foreach ($config->layers as $index => $layer) {
            if (str_contains($layer, '/')) {
                continue;
            }
            if (in_array($layer, $directories, true)) {
                return $index;
            }
            foreach ($namespaces as $namespace) {
                if (str_contains($namespace, '\\' . trim(strtolower($layer), '\\') . '\\')) {
                    return $index;
                }
            }
        }

        return null;
    }
}
