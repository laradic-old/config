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
namespace Laradic\Tests\Config\Loaders;

use Laradic\Config\Loaders\FileLoader;
use Laradic\Dev\Traits\LaravelTestCaseTrait;
use Laradic\Dev\Traits\ServiceProviderTestCaseTrait;
use Laradic\Tests\Config\ConfigTestCase;
use Mockery as m;

/**
 * Class StrTest
 *
 * @package Laradic\Test\Config
 */
class FileLoaderTest extends ConfigTestCase
{

    protected function _createLoadFileTest($path, $name, $ext)
    {
        $result = [ 'foo' => 'bar' ];
        $fs     = m::mock('Illuminate\Filesystem\Filesystem')
            ->shouldIgnoreMissing()
            ->shouldReceive('exists')
            ->zeroOrMoreTimes()
            ->with($path . '/' . $name . $ext)
            ->andReturn(true)
            ->getMock();

        $fs->shouldReceive('getRequire')
            ->zeroOrMoreTimes()
            ->with($path . '/' . $name . $ext)
            ->andReturn($result)
            ->getMock();

        $fs->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->with($path . '/' . $name . $ext)
            ->andReturn('foo: bar')
            ->getMock();

        $loader = new FileLoader($fs, $path);
        $this->assertEquals($result, $loader->load('', $name));
    }

    public function testLoadsPhpFileFromPath()
    {
        $this->_createLoadFileTest(__DIR__ . '/test', 'laradic_fake_test', '.php');
    }

    public function testLoadsYmlFileFromPath()
    {
        $this->_createLoadFileTest(__DIR__ . '/test', 'laradic_fake_test', '.yml');
    }
}
