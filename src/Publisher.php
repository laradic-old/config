<?php
/**
 * Part of the Radic packages.
 */
namespace Laradic\Config;

use Illuminate\Filesystem\Filesystem;
/**
 * Class Publisher
 *
 * @package     Laradic\Config
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 */
class Publisher
{

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    protected $config;

    protected $output = [];

    /**
     * Create a new publisher instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     * @param  string $publishPath
     */
    public function __construct(Filesystem $files, Repository $config)
    {
        $this->files  = $files;
        $this->config = $config;
    }

    public function publishAll()
    {
        foreach ($this->getPackages() as $package)
        {
            $this->publish($package);
        }

        return $this;
    }

    public function publish($package)
    {
        if ( ! $this->isPublished($package) )
        {
            if(! $source = $this->getSourcePath($package))
            {
                return $this;
            }

            $destination = $this->getDestinationPath($package);
            $this->ensureDestination($package);
            $this->files->copyDirectory($source, $destination);
            $this->output[] = "Published {$package}";
        }

        return $this;
    }

    public function output()
    {
        return implode("\n", $this->output) . "\n";
    }

    protected function ensureDestination($package)
    {
        $destination = $this->getDestinationPath($package);
        if ( ! $this->files->isDirectory($destination) )
        {
            $this->files->makeDirectory($destination, 0777, true);
        }
    }

    protected function getDestinationPath($package)
    {
        return config_path('packages/' . $package);
    }

    protected function isPublished($package)
    {
        return $this->files->isDirectory($this->getDestinationPath($package));
    }

    protected function getPackages()
    {
        $cbs = $this->config->getAfterLoadCallbacks();
        if ( empty($cbs) )
        {
            return [];
        }

        return array_keys($cbs);
    }

    protected function getSourcePath($package)
    {
        // get package dir
        $packageDirs = [base_path($package), base_path('vendor/' . $package), base_path('packages/' . $package)];
        $packageDir  = null;
        foreach ($packageDirs as $dir)
        {
            if ( $this->files->isDirectory($dir) )
            {
                $packageDir = $dir;
                break;
            }
        }
        if ( $packageDir === null )
        {
            return $packageDir;
        }

        // get config dir
        $configDirs = ['config', 'resources/config'];
        $configDir  = null;
        foreach ($configDirs as $dir)
        {
            if ( $this->files->isDirectory($packageDir . '/' . $dir) )
            {
                $configDir = $packageDir . '/' . $dir;
                break;
            }
        }

        return $configDir;
    }
}