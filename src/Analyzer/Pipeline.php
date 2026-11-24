<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Analyzer;

use RuntimeException;

final class Pipeline
{
    public static function check(array $payload): array
    {
        $checkout = dirname(__DIR__, 3);
        $local = is_file("$checkout/degra/go.mod");
        $directory = getenv('IMPORT_LAYERS_TOOLS');
        $installed = $directory ? "$directory/degra" : self::findOnPath('degra');
        $command = $installed !== null
            ? [$installed]
            : ($local ? ['go', '-C', "$checkout/degra", 'run', '.'] : ['degra']);
        array_push($command, '--project', $local ? $checkout : '');

        // File streams let the child write either output without blocking on pipes.
        $input = tmpfile();
        $stdout = tmpfile();
        $stderr = tmpfile();
        if ($input === false || $stdout === false || $stderr === false) {
            throw new RuntimeException('Cannot create temporary pipeline streams');
        }
        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);
            if (fwrite($input, $json) !== strlen($json) || !rewind($input)) {
                throw new RuntimeException('Cannot write pipeline input');
            }
            $process = proc_open($command, [$input, $stdout, $stderr], $pipes);
            if (!is_resource($process)) {
                throw new RuntimeException('Cannot start degra');
            }
            $status = proc_close($process);
            if ($status !== 0) {
                rewind($stderr);
                throw new RuntimeException(trim(stream_get_contents($stderr)) ?: "degra exited with status $status");
            }
            rewind($stdout);
            return json_decode(stream_get_contents($stdout), true, flags: JSON_THROW_ON_ERROR);
        } finally {
            fclose($input);
            fclose($stdout);
            fclose($stderr);
        }
    }

    private static function findOnPath(string $name): ?string
    {
        foreach (explode(PATH_SEPARATOR, getenv('PATH') ?: '') as $directory) {
            $path = "$directory/$name";
            if (is_file($path) && is_executable($path)) {
                return $path;
            }
        }
        return null;
    }
}
