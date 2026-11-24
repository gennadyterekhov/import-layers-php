<?php declare(strict_types=1);

use Gennadyterekhov\ImportLayersPhp\Analyzer\App;

if (isset($GLOBALS['_composer_autoload_path'])) {
    require_once $GLOBALS['_composer_autoload_path'];
} else if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else if (file_exists(__DIR__ . '/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
} else {
    fwrite(STDERR, "Could not find autoloader\n");
    exit(2);
}

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--') {
        break;
    }
    if ($argument === '-h' || $argument === '--help') {
        echo "Usage: import-layers-php [-c|--config path] [--graph] [path ...]\n";
        exit(0);
    }
}

try {
    $app = App::createApp($argc, $argv);
    exit($app->outputResult($app->analyze()));
} catch (InvalidArgumentException | RuntimeException | JsonException $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(2);
}
