<?php

declare(strict_types=1);

namespace Gennadyterekhov\ImportLayersPhp\Config;

use Gennadyterekhov\ImportLayersPhp\Dto\Config;

final readonly class ConfigService
{
    public function __construct() {}

    public function readConfigFromFileIntoDto(): Config
    {
        return new Config();
    }
}
