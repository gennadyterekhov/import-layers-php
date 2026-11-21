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

By default, the tool reads [import_layers.json](https://github.com/gennadyterekhov/import-layers-php/blob/main/import_layers.json) from the project root.
Use `-c` to select a different JSON file.

~~~sh
./analyzers/import-layers-php -c config/layers.json
./analyzers/import-layers-php -c config/layers.json src
~~~

The config path can be absolute or relative to the current working directory.
The positional directory is used when the config omits **rootDir**.

