<?php

declare(strict_types=1);

namespace Tests\testdata\Unit\Analyzer\FileAnalyzerTest\HighUsesLow\High;

use Tests\testdata\Unit\Analyzer\FileAnalyzerTest\HighUsesLow\Low\Low;

final readonly class High
{
    public const string A = 'a';

    public function do()
    {
        return Low::A;
    }
}