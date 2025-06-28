<?php

namespace Agnula\LatexForLaravel\Tests;

use Agnula\LatexForLaravel\LatexForLaravelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LatexForLaravelServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
    }
}
