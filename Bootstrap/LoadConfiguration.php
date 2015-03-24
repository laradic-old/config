<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 */
namespace Laradic\Config\Bootstrap;

use Laradic\Config\Loaders\DatabaseLoader;
use Laradic\Config\Loaders\FileLoader;
use Laradic\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application;

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
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {


        $env = $app->environment();
        $filesystem = new Filesystem;
        $loader = new FileLoader($filesystem, $app['path.config']);
        $app->instance('config', $config = new Repository($loader, $filesystem, $env));
        $loader->setRepository($config);

        $configuredLoader = $app['config']->get('laradic_config.loader');
        if(isset($configuredLoader) && $configuredLoader !== 'file'){
            if($configuredLoader === 'db')
            {
                $loader = new DatabaseLoader($filesystem, $app['path.config']);
                $app->instance('config', $config = new Repository($loader, $filesystem, $env));
                $app->booted(function() use ($app, $loader, $config){
                    $loader->setDatabase($app['db']->connection());
                    $loader->setDatabaseTable($app['config']->get('laradic_config.loaders.db.table'));
                });
            }
            $loader->setRepository($config);
        }

        if (file_exists($cached = $app->getCachedConfigPath()) && ! $app->runningInConsole()) {
            $items = require $cached;

            $config->set($items);
        }
        date_default_timezone_set($config['app.timezone']);

        mb_internal_encoding('UTF-8');
    }
}
