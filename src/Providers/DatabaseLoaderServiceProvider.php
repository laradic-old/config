<?php
/**
 * Part of the Radic packages.
 */
namespace Laradic\Config\Providers;

use Illuminate\Support\ServiceProvider;
use Laradic\Config\Loaders\CompositeLoader;

class DatabaseLoaderServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		#$this->package('cartalyst/composite-config', 'cartalyst/composite-config');

        return;
		$originalLoader = $this->app['config']->getLoader();

		// We will grab the new loader and syncronize all of the namespaces.
		$compositeLoader = $this->app['config.loader.composite'];
		foreach ($originalLoader->getNamespaces() as $namespace => $hint)
		{
			$compositeLoader->addNamespace($namespace, $hint);
		}

        $table = $this->app['config']['laradic/config::loaders.db.table'];

		// Now we will set the config loader instance.
		#unset($this->app['config.loader.composite']);
		#$this->app['config']->setLoader($compositeLoader);

		// Set the database property on the composite loader so it will now
		// merge database configuration with file configuration.
        $compositeLoader = $this->app['config']->getLoader();
		if ($this->databaseIsReady($table))
		{
			$compositeLoader->setDatabase($this->app['db']->connection());
			$compositeLoader->setDatabaseTable($table);
			$compositeLoader->cacheConfigs();
		}

		// We'll also set the repository
		$compositeLoader->setRepository($this->app['config']);

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$compositeLoader = new CompositeLoader($this->app['files'], $this->app['path'].'/config');

		$this->app->instance('config.loader.composite', $compositeLoader);
	}

	/**
	 * Check for config table.
	 *
	 * @param  string  $table
	 * @return bool
	 */
	protected function databaseIsReady($table)
	{
		try {
			if ($this->app['db']->connection()->getSchemaBuilder()->hasTable($table))
			{
				return true;
			}
		}
		catch (\PDOException $e)
		{
			return false;
		}
	}

}
