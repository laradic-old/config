<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 */
namespace Laradic\Config;

use Laradic\Support\ServiceProvider;
use Laradic\Config\Traits\ConfigProviderTrait;
use Illuminate\Console\Application as Artisan;

/**
 * Class ConfigServiceProvider
 *
 * @package     Laradic\Config
 * @author      Robin Radic
 * @author      Mior Muhammad Zaki
 * @author      Taylor Otwell
 * @license     MIT
 * @copyright   Check the embedded LICENSE file
 */
class ConfigServiceProvider extends ServiceProvider
{
    protected $configFiles = ['laradic_config'];

    /** @var string */
    protected $dir = __DIR__;

    protected $resourcesPath = '../resources';

    /** @inheritdoc */
    public function register()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = parent::register();

        $this->publishes([
            __DIR__.'/../resources/database/migrations/' => base_path('/database/migrations')
        ], 'migrations');

        if($app->make('config') instanceof \Laradic\Config\Repository)
        {
            $this->registerPublisher();
        }
    }

    public function registerPublisher()
    {
        $this->app['events']->listen('artisan.start', function (Artisan $artisan)
        {
            $args = $GLOBALS['argv'];
            if ( in_array('vendor:publish', $args) )
            {
                /** @var \Illuminate\Foundation\Application $app */
                $app    = $artisan->getLaravel();
                $config = $app->make('config');
                if ( ! $config instanceof \Laradic\Config\Repository )
                {
                    return;
                }
                $config->publish();
            }
        });
    }

    public function provides()
    {
        return ['config'];
    }
}
