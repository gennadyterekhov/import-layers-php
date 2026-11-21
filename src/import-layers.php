<?php declare(strict_types=1);

// Try Composer's global path first
use Gennadyterekhov\ImportLayersPhp\Analyzer\App;

if (isset($GLOBALS['_composer_autoload_path'])) {
    require_once $GLOBALS['_composer_autoload_path'];
} // Fallback to relative path (for development)
else if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} // Fallback to typical vendor location when installed as dependency
else if (file_exists(__DIR__ . '/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
} else {
    fwrite(STDERR, "Could not find autoloader\n");
    exit(1);
}

$app = App::createApp($argc, $argv);
$app->outputResult($app->analyze());
