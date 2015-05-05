<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 */
namespace Laradic\Config\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Laradic\Config\Loaders\DatabaseLoader;
use Laradic\Config\Loaders\FileLoader;
use Laradic\Config\Repository;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LoadConfiguration
 *
 * @package     Laradic\Config
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 */
class LoadConfiguration
{

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {


        /** @var \Illuminate\Foundation\Application $app*/

        $env        = $app->environment();
        $filesystem = $this->files = new Filesystem;
        $loader     = new FileLoader($filesystem, $app[ 'path.config' ]);
        $config     = new Repository($loader, $filesystem, $env);
        $loader->setRepository($config);
        $app->instance('config', $config);

        $configuredLoader = $app[ 'config' ]->get('laradic_config.loader');
        if ( isset($configuredLoader) && $configuredLoader !== 'file' )
        {
            if ( $configuredLoader === 'db' )
            {
                $loader = new DatabaseLoader($filesystem, $app[ 'path.config' ]);
                $config->setLoader($loader);
                $app->booted(function () use ($app, $loader, $config)
                {
                    $loader->setDatabase($app[ 'db' ]->connection());
                    $loader->setDatabaseTable($app[ 'config' ]->get('laradic_config.loaders.db.table'));
                });
                $loader->setRepository($config);
            }
        }

        if ( file_exists($cached = $app->getCachedConfigPath()) && ! $app->runningInConsole() )
        {
            $items = require $cached;

            $loadedFromCache = true;
        }
        if ( ! isset($loadedFromCache) )
        {
           # $this->loadConfigurationFiles($app, $config);
        }

        date_default_timezone_set($config[ 'app.timezone' ]);

        mb_internal_encoding('UTF-8');
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @param  \Illuminate\Contracts\Config\Repository      $config
     * @return void
     */
    protected function loadConfigurationFiles(Application $app, \Illuminate\Contracts\Config\Repository $config)
    {
        foreach ( $this->getConfigurationFiles($app) as $key => $path )
        {
            $config->set($key, require $path);
        }

        foreach ( $this->getConfigurationFilesYml($app) as $key => $path )
        {
            $c = Yaml::parse($this->files->get($path));
            $config->set($key, $c);
        }

    }

    /**
     * Get all of the configuration files for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {
        $files = [ ];

        foreach ( Finder::create()->files()->name('*.php')->in($app->configPath()) as $file )
        {
            $nesting = $this->getConfigurationNesting($file);

            $files[ $nesting . basename($file->getRealPath(), '.php') ] = $file->getRealPath();
        }

        return $files;
    }

    /**
     * Get all of the configuration files for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @return array
     */
    protected function getConfigurationFilesYml(Application $app)
    {
        $files = [ ];

        foreach ( Finder::create()->files()->name('*.yml')->in($app->configPath()) as $file )
        {
            $nesting = $this->getConfigurationNesting($file);

            $files[ $nesting . basename($file->getRealPath(), '.yml') ] = $file->getRealPath();
        }

        return $files;
    }

    /**
     * Get the configuration file nesting path.
     *
     * @param  \Symfony\Component\Finder\SplFileInfo $file
     * @return string
     */
    private function getConfigurationNesting(SplFileInfo $file)
    {
        $directory = dirname($file->getRealPath());

        if ( $tree = trim(str_replace(config_path(), '', $directory), DIRECTORY_SEPARATOR) )
        {
            $tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree) . '.';
        }

        return $tree;
    }
}
