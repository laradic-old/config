<?php
/**
 * Part of the Radic packages.
 */
namespace Laradic\Config;

use Illuminate\Console\Application as Artisan;
use Illuminate\Support\ServiceProvider;

/**
 * Class PublisherServiceProvider
 *
 * @package     Laradic\Config
 * @subpackage  Providers
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 */
class PublisherServiceProvider extends ServiceProvider
{

    /** @inheritdoc */
    public function register()
    {
        $this->app->bind('config.publisher', function ($app)
        {
            return new Publisher($app['files'], $app['config']);
        });

        $this->app['events']->listen('artisan.start', function (Artisan $artisan)
        {

            $args = $GLOBALS['argv'];
            if ( in_array('vendor:publish', $args) )
            {
                /** @var \Illuminate\Foundation\Application $app */
                $app    = $artisan->getLaravel();

                $config = $app->make('config');
                if ( ! $config instanceof Repository )
                {
                    return;
                }

                $publisher = $app->make('config.publisher');
                print $publisher->publishAll()->output();
            }
        });
    }
}