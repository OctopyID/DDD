<?php

namespace Octopy\DDD\Tests;

use Illuminate\Foundation\Application;
use Octopy\DDD\DDDServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @param  Application $app
     * @return string[]
     */
    protected function getPackageProviders($app) : array
    {
        return [
            DDDServiceProvider::class,
        ];
    }
}
