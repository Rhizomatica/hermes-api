<?php

use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    #[\Override]
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}
