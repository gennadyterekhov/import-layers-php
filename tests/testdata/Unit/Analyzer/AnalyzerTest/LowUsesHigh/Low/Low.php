<?php

declare(strict_types=1);

namespace Tests\testdata\Unit\Analyzer\FileAnalyzerTest\LowUsesHigh\Low;
use Tests\testdata\Unit\Analyzer\FileAnalyzerTest\LowUsesHigh\High\High;

final readonly class Low
{
    public const string A = 'a';

    public function do()
    {
        return High::A;
    }
}