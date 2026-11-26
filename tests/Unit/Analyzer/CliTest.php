<?php

declare(strict_types=1);

namespace Tests\Unit\Analyzer;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class CliTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/import-layers-php-' . bin2hex(random_bytes(8));
        mkdir($this->root);
    }

    protected function tearDown(): void
    {
        $entries = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($entries as $entry) {
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($this->root);
    }

    public function testFindsYamlFromSubdirectoryAndChecksSeveralPaths(): void
    {
        $this->write('import_layers.yaml', "layers: [domain, application]\n");
        $this->write('src/domain/item.php', '<?php namespace Domain; use Vendor\\External;');
        $this->write('src/application/item.php', '<?php namespace Application;');

        [$status, $stdout, $stderr] = $this->runCli(['domain', 'application'], 'src');

        self::assertSame([0, "OK\n", ''], [$status, $stdout, $stderr]);
    }

    public function testReportsEveryGroupedUseAndBuildsDirectDependencyGraph(): void
    {
        $this->write('config/layers.json', json_encode([
            'layers' => ['src/domain', 'src/application'],
            'rootDir' => 'src/domain',
            'debug' => true,
            'ignore_tests' => true,
        ], JSON_THROW_ON_ERROR));
        $this->write(
            'src/domain/item.php',
            '<?php namespace Domain; use Api\\{Thing, function run, const VALUE};',
        );
        $this->write('src/domain/itemTest.php', '<?php !');
        $this->write('src/application/Thing.php', '<?php namespace Api; class Thing {}');
        $this->write('src/application/functions.php', '<?php namespace Api; function run() {} const VALUE = 1;');

        [$status, $stdout, $stderr] = $this->runCli(['--config', 'config/layers.json', '--graph', 'not-a-path']);

        self::assertSame(1, $status, $stderr);
        self::assertSame([
            'nodes' => ['src/application/Thing.php', 'src/application/functions.php', 'src/domain/item.php'],
            'edges' => [
                ['from' => 'src/domain/item.php', 'to' => 'src/application/Thing.php'],
                ['from' => 'src/domain/item.php', 'to' => 'src/application/functions.php'],
            ],
        ], json_decode($stdout, true, flags: JSON_THROW_ON_ERROR));
        self::assertStringContainsString('processing file src/domain/item.php', $stderr);
        self::assertStringNotContainsString('itemTest.php', $stderr);
        self::assertSame(3, substr_count($stderr, 'cannot import package from lower layer'));
    }

    public function testParseAndConfigurationErrorsHaveFailureStatus(): void
    {
        $this->write('import_layers.json', '{"layers":["domain","application"],"debug":"yes"}');
        [$status, $stdout, $stderr] = $this->runCli([]);
        self::assertSame(2, $status);
        self::assertSame('', $stdout);
        self::assertStringContainsString('debug must be a boolean', $stderr);

        $this->write('import_layers.json', '{"layers":["domain","application"]}');
        $this->write('src/broken.php', '<?php !');
        [$status, $stdout, $stderr] = $this->runCli([]);
        self::assertSame(2, $status);
        self::assertSame('', $stdout);
        self::assertStringContainsString('src/broken.php:1:', $stderr);
    }

    public function testChecksStaticallyResolvableIncludesInsideFunctions(): void
    {
        $this->write('import_layers.json', '{"layers":["Domain","Application"]}');
        $this->write('src/Domain/item.php', <<<'SRC'
            <?php namespace Domain;
            function run(string $dynamic) {
                require_once __DIR__ . '/../Application/implementation.php';
                include $dynamic;
                include '../Application/implementation.php';
            }
            SRC);
        $this->write('src/Application/implementation.php', '<?php namespace Application;');

        [$status, $stdout, $stderr] = $this->runCli(['--graph', 'src/Domain']);

        self::assertSame(1, $status, $stderr);
        self::assertSame(2, substr_count($stderr, 'cannot import package from lower layer'));
        self::assertStringContainsString('src/Domain/item.php:3 -> src/Application/implementation.php', $stderr);
        self::assertSame([
            ['from' => 'src/Domain/item.php', 'to' => 'src/Application/implementation.php'],
        ], json_decode($stdout, true, flags: JSON_THROW_ON_ERROR)['edges']);
    }

    private function write(string $path, string $content): void
    {
        $path = $this->root . '/' . $path;
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $content);
    }

    private function runCli(array $arguments, string $directory = '.'): array
    {
        $entry = dirname(__DIR__, 3) . '/src/import-layers.php';
        $process = proc_open(
            [PHP_BINARY, $entry, ...$arguments],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->root . '/' . $directory,
        );
        self::assertIsResource($process);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return [proc_close($process), $stdout, $stderr];
    }
}
