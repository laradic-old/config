<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 *
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 */
namespace Laradic\Tests\Config;

use Laradic\Config\Loaders\FileLoader;
use Laradic\Dev\Traits\LaravelTestCaseTrait;
use Laradic\Dev\Traits\ServiceProviderTestCaseTrait;
use Mockery as m;

/**
 * Class StrTest
 *
 * @package Laradic\Test\Config
 */
class ConfigServiceProviderTest extends ConfigTestCase
{
    use ServiceProviderTestCaseTrait;

    protected function getServiceProviderClass($app)
    {
        return 'Laradic\Config\ConfigServiceProvider';
    }

    public function testLoader()
    {
        $path = __DIR__ . '/fixture';
        $result = [ 'foo' => 'bar' ];
        $fs = m::mock('Illuminate\Filesystem\Filesystem')
            ->shouldIgnoreMissing()
            ->shouldReceive('exists')
            ->zeroOrMoreTimes()
            ->with($path . '/laradic_test.php')
            ->andReturn(true)
            ->getMock();

        $fs->shouldReceive('getRequire')
            ->zeroOrMoreTimes()
            ->with($path . '/laradic_test.php')
            ->andReturn($result)
            ->getMock();

        $fileLoader = new FileLoader($fs, $path);
        $this->assertEquals($result, $fileLoader->load('', 'laradic_test'));
    }


}
