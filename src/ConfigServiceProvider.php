<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 */
namespace Laradic\Config;

use Illuminate\Support\ServiceProvider;
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
    use ConfigProviderTrait;

    /** @inheritdoc */
    public function register()
    {
        $this->addConfigComponent('laradic/config', 'laradic/config', realpath(__DIR__ . '/../config'));
        $this->app->register('Laradic\Config\Providers\PublisherServiceProvider');
    }
}
