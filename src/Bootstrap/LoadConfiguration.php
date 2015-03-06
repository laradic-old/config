<?php namespace Laradic\Config\Bootstrap;

use Laradic\Config\Loaders\CompositeLoader;
use Laradic\Config\Loaders\FileLoader;
use Laradic\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application;

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



        $fileLoader = new FileLoader(new Filesystem, $app['path.config']);
        $env = $app->environment();
        $app->instance('config', $config = new Repository($fileLoader, $env));

        $configuredLoader = $app['config']->get('laradic_config.loader');
        if(isset($configuredLoader) && $configuredLoader !== 'file'){
            if($configuredLoader === 'db')
            {
                $loader = new CompositeLoader(new Filesystem, $app['path.config']);
                $app->instance('config', $config = new Repository($loader, $env));
                $loader->setRepository($config);
                $app->booted(function() use ($app, $loader, $config){
                    $loader->setDatabase($app['db']->connection());
                    $loader->setDatabaseTable($app['config']->get('laradic_config.loaders.db.table'));
                    $loader->cacheConfigs();
                });

            }
        }

        if (file_exists($cached = $app->getCachedConfigPath()) && ! $app->runningInConsole()) {
            $items = require $cached;

            $config->set($items);
        }
        date_default_timezone_set($config['app.timezone']);

        mb_internal_encoding('UTF-8');
    }
}
