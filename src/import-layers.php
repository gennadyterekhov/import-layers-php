<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Gennadyterekhov\ImportLayersPhp\Analyzer\Analyzer;
use Gennadyterekhov\ImportLayersPhp\Config\ConfigService;
use PhpParser\ParserFactory;

$service = new ConfigService();
$config = $service->readConfigFromFileIntoDto();

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
