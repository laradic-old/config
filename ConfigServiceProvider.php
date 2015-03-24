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
        parent::register();

        $this->publishes([
            __DIR__.'/../database/migrations/' => base_path('/database/migrations')
        ], 'migrations');
        /** @var \Illuminate\Foundation\Application */
        $app = $this->app;

        if($app->make('config') instanceof \Laradic\Config\Repository)
        {
            var_dump('sadfsd');
            $this->app->register('Laradic\Config\Providers\PublisherServiceProvider');
        }
    }
}
