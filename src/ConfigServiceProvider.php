<?php
/**
 * Part of the Radic packages.
 */
namespace Laradic\Config;

use Illuminate\Support\ServiceProvider;
use Laradic\Config\Traits\ConfigProviderTrait;

/**
 * Class ConfigServiceProvider
 *
 * @package     Laradic\Debug
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 */
class ConfigServiceProvider extends ServiceProvider
{
    use ConfigProviderTrait;

    /** @inheritdoc */
    public function register()
    {
        $this->addConfigComponent('laradic/config', 'laradic/config', realpath(__DIR__ . '/../config'));
        $this->app->register('Laradic\Config\Providers\PublisherProvider');
    }
}