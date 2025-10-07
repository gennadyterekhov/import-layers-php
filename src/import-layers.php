<?php declare(strict_types=1);

// Try Composer's global path first
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

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Config\ConfigService;
use PhpParser\ParserFactory;

$dirToCheck = '';
if ($argc > 1) {
    $dirToCheck = $argv[1];
}
$service = new ConfigService();
$config = $service->readConfigFromFileIntoDto($dirToCheck);

$parser = (new ParserFactory())->createForHostVersion();
$analyzer = new Analyzer($parser, $config);
$result = $analyzer->analyze();

if (count($result->errors) > 0) {
    foreach ($result->errors as $error) {
        echo $error . PHP_EOL;
    }
} else {
    echo 'OK' . PHP_EOL;
}
echo PHP_EOL;
