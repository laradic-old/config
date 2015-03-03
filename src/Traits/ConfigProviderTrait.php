<?php
/**
 * Part of the Radic packages.
 */
namespace Laradic\Config\Traits;

use Laradic\Config\Contracts\PackageRepository;
use ReflectionClass;

/**
 * Class ConfigProviderTrait
 *
 * @package     Laradic\Config\Traits
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-${YEAR}, Robin Radic
 * @link        http://radic.mit-license.org
 */
trait ConfigProviderTrait
{

    public function addConfig($package, $namespace = null, $path = null)
    {
        $namespace = $this->getConfigNamespace($package, $namespace);
        /** @var \Illuminate\Filesystem\Filesystem $files */
        $files = $this->app['files'];
        $path  = $path ?: $this->guessConfigPath();

        if ($files->isDirectory($config = $path . '/config'))
        {
            $this->addConfigComponent($package, $namespace, $config);
        }
    }

    public function addConfigComponent($package, $namespace, $path)
    {
        $config = $this->app['config'];
        if ($config instanceof PackageRepository)
        {
            $config->package($package, $path, $namespace);
        }
    }

    public function guessConfigPath()
    {
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
}