<?php namespace Orchestra\Extension\TestCase;

use Mockery as m;
use Orchestra\Extension\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase {

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    public function testMakeMethod()
    {
        $files = m::mock('Illuminate\Contracts\Filesystem\Filesystem');
        $builder = new Builder($files);

        $builder->make();
    }

}