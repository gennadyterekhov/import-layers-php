# import-layers-php

import-layers-php is  a library to check if imports are correctly layered


in other words - check that higher layer packages do not depend on lower layer packages (dependency rule from clean architecture)

[see also version for golang projects](https://github.com/gennadyterekhov/import-layers-go)

## example

config

    {
        "layers": [
            "high",
            "low"
        ]
    }


php

    <?php
    
    declare(strict_types=1);
    
    namespace Tests\testdata\Unit\Analyzer\AnalyzerTest\HighUsesLow\High;
    
    // returns error: `cannot import package from lower layer`
    use Tests\testdata\Unit\Analyzer\AnalyzerTest\HighUsesLow\Low\Low; 
    
    final readonly class High
    {
        public const string A = 'a';
    
        public function do()
        {
            return Low::A; 
        }
    }


## running in your repo

download bin from releases and place it in `analyzers` for example  
run

    ./analyzers/import-layers-php

## config

See example config in [import_layers.json](https://github.com/gennadyterekhov/import-layers-php/blob/main/import_layers.json)  
Config file must be in the same directory as a `composer.json` file.  
It must be named `import_layers.json`.  
Config file name and location are not configurable.
