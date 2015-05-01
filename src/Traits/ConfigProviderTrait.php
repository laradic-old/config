<?php
/**
 * Part of the Radic packages.
 */
namespace Laradic\Config\Traits;

use Laradic\Config\Contracts\PackageRepository;
use Laradic\Config\Repository;
use Laradic\Support\Path;
use ReflectionClass;

/**
 * Class ConfigProviderTrait
 *
 * @package     Laradic\Config
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 * @property \Illuminate\Foundation\Application $app
 */
trait ConfigProviderTrait
{

    public function addConfig($package, $namespace = null, $path = null)
    {
        $namespace = $this->getConfigNamespace($package, $namespace);
        $files = $this->app->make('files');
        $path  = $path ?: $this->guessConfigPath();

        if ($files->isDirectory($config = $path . '/config'))
        {
            $this->addConfigComponent($package, $namespace, $config);
        }
    }

    /**
     * addConfigComponent
     *
     * @param $package
     * @param $namespace
     * @param $path
     * @return \Laradic\Config\Repository
     */
    public function addConfigComponent($package, $namespace, $path)
    {
        $config = $this->app->make('config');
        if ($config instanceof Repository)
        {
            $config->package($package, $path, $namespace);
            $config->addPublisher($package, $path);
            return array_dot($config->get($namespace . '::config'));
        }

    }

    public function guessConfigPath()
    {
        if(isset($this->dir) and isset($this->resourcesPath))
        {
            return Path::join($this->dir, $this->resourcesPath, 'config');
        }
        $path = (new ReflectionClass($this))->getFileName();

        return realpath(dirname($path) . '/../../');
    }

    protected function getConfigNamespace($package, $namespace)
    {
        if (is_null($namespace))
        {
            list(, $namespace) = explode('/', $package);
        }

        return $namespace;
    }

    protected function addPublisher($package, $sourceDir)
    {
        /** @var \Laradic\Config\Repository $config */
        $config = $this->app['config'];
        $config->addPublisher($package, $sourceDir);
    }
}
