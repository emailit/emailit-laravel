<?php

namespace Emailit\Laravel\Tests;

use Emailit\Laravel\EmailitServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            EmailitServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Emailit' => \Emailit\Laravel\Facades\Emailit::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('emailit.api_key', 'test-api-key');
    }
}
