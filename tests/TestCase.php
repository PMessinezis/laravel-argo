<?php

namespace Theomessin\Argo\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Theomessin\Argo\ArgoFacade;

class TestCase extends BaseTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        // Use our custom storage path for testing.
        $app->useStoragePath(realpath(__DIR__ . '/storage/'));
    }

    protected function getPackageProviders($app)
    {
         return ['Theomessin\\Argo\\ServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Argo' => ArgoFacade::class,
        ];
    }
}
