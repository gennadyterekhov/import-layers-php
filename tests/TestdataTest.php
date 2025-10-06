<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Helpers\Testdata;

final class TestdataTest extends TestCase
{
    public function testCanGetPathToTestdata(): void
    {
        [$path, $onlyFuncName] = Testdata::getPathAndFuncName(__METHOD__);

        self::assertEquals('tests/testdata/TestdataTest/', $path);
        self::assertEquals('testCanGetPathToTestdata', $onlyFuncName);
    }
}
