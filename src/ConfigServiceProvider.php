<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 */
namespace Laradic\Config;

use Laradic\Support\ServiceProvider;
use Laradic\Config\Traits\ConfigProviderTrait;

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

    /** @inheritdoc */
    public function register()
    {
       # $this->addConfigComponent('laradic/config', 'laradic/config', realpath(__DIR__ . '/../config'));
        $this->publishes([
            __DIR__.'/../database/migrations/' => base_path('/database/migrations')
        ], 'migrations');


        $this->app->register('Laradic\Config\Providers\PublisherServiceProvider');

        if($this->app->config->get('laradic_config.loader') === 'db')
        {
            $this->app->register('Laradic\Config\Providers\DatabaseLoaderServiceProvider');
        }
    }
}
