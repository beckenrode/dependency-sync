<?php

namespace DependencySync\Tests;

use DependencySync\DependencySyncServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [DependencySyncServiceProvider::class];
    }
}
