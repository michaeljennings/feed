<?php

namespace Michaeljennings\Feed\Tests;

use Michaeljennings\Feed\FeedServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class DbTestCase extends OrchestraTestCase
{
    /**
     * Setup DB before each test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => '../../../../migrations']);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app)
    {
        return [
            FeedServiceProvider::class,
        ];
    }
}