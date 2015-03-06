<?php namespace Laradic\Config\Loaders;
/**
 * Part of the Composite Config package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Composite Config
 * @version    1.0.2
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Database\Connection as DatabaseConnection;
use Illuminate\Filesystem\Filesystem;
use Laradic\Config\Repository;

class CompositeLoader extends FileLoader implements LoaderInterface {

	/**
	 * The database instance.
	 *
	 * @var Illuminate\Database\Connection
	 */
	protected $database;

	/**
	 * The config database table.
	 *
	 * @var string
	 */
	protected $databaseTable;

	/**
	 * The config repository instance.
	 *
	 * @var Illuminate\Config\Repository
	 */
	protected $repository;

	/**
	 * Array of cached items, persisted between loading
	 * and cascading configuration so we can override
	 * cascaded file configuration with databas configuration.
	 *
	 * @var array
	 */
	protected $cachedItems = array();

	/**
	 * Cached database items.
	 *
	 * @var array
	 */
	protected $cachedConfigs = array();

	/**
	 * Sets a config value for the loader (i.e. permanently).
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  string  $environment
	 * @return void
	 */
	public function set($key, $value = null, $environment = null)
	{
		if ( ! isset($this->repository))
		{
			throw new \RuntimeException("Repository is required to set a config value. Use persist() instead.");
		}

		list($namespace, $group, $item) = $this->repository->parseKey($key);
		$environment = $environment ? $environment : $this->repository->getEnvironment();

		$this->persist($environment, $group, $item, $value, $namespace);
	}

	/**
	 * Load the given configuration group.
	 *
	 * @param  string  $environment
	 * @param  string  $group
	 * @param  string  $namespace
	 * @return array
	 */
	public function load($environment, $group, $namespace = null)
	{
		// If the database has not been set on the config
		// loader, we simply default to our parent - file
		// loading.
		if ( ! isset($this->database))
		{
			return parent::load($environment, $group, $namespace);
		}

        #redit
		$items = []; #parent::load($environment, $group, $namespace); ## array();

		// Environment specific configs
		$result = array_get($this->cachedConfigs, "{$environment}.{$namespace}.{$group}", array());

		// Merge global configs
		$result = array_merge($result, array_get($this->cachedConfigs, "*.{$namespace}.{$group}", array()));

		if ( ! empty($result))
		{
			foreach ($result as $result)
			{
				array_set($items, $result->item, $this->parseValue($result->value));
			}
		}

		$cacheKey = $this->getCacheKey($environment, $group, $namespace);
		array_set($this->cachedItems, $cacheKey, $items);

		$parentItems = parent::load($environment, $group, $namespace);

		return array_replace_recursive($parentItems, $items);
	}

	/**
	 * Apply any cascades to an array of package options.
	 *
	 * @param  string  $env
	 * @param  string  $package
	 * @param  string  $group
	 * @param  array   $items
	 * @return array
	 */
	public function cascadePackage($env, $package, $group, $items)
	{
		// First, we will retrieve the items from the
		// parent file loader.
		$items = parent::cascadePackage($env, $package, $group, $items);

		// When we queried before, we cached our results. This
		// is because database results must override all cascaded
		// file results. We'll now replace the results again here
		// so our database rules all!
		$cacheKey    = $this->getCacheKey($env, $group, $package);
		$cachedItems = array_get($this->cachedItems, $cacheKey, array());

		return array_replace_recursive($items, $cachedItems);
	}

	/**
	 * Persist the given configuration to the database.
	 *
	 * @param  string  $environment
	 * @param  string  $group
	 * @param  string  $name
	 * @param  mixed   $value
	 * @param  string  $namespace
	 * @return void
	 */
	public function persist($environment, $group, $item, $value = null, $namespace = null)
	{
		// If there is no databse, we'll not persist anything which will make
		// the configuration act as if this package was not installed.
		if ( ! isset($this->database)) return;

		$query = $this
			->getGroupQuery($environment, $group, $namespace, false)
			->where('item', '=', $item);

		// Firstly, we'll see if the configuration exists
		$existing = $query->first();

		if ($existing)
		{
			if (isset($value))
			{
				// We'll update an existing record
				$query->update(array('value' => $this->prepareValue($value)));
			}
			else
			{
				$query->delete();
			}
		}
		elseif (isset($value))
		{
			// Prepare our data
			$data = compact('environment', 'group', 'item');
			$data['value'] = $this->prepareValue($value);
			if (isset($namespace)) $data['namespace'] = $namespace;

			$this
				->database->table($this->databaseTable)
				->insert($data);
		}

		// We will remove the cache from our repository so that it's
		// forced to refresh it next time.
		$this->removeRepositoryCache($group, $item, $namespace);
	}

	/**
	 * Returns the database connection.
	 *
	 * @return Illuminate\Database\Connection
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * Sets the database connection.
	 *
	 * @param  Illuminate\Database\Connection  $database
	 * @return void
	 */
	public function setDatabase(DatabaseConnection $database)
	{
		$this->database = $database;
	}

	/**
	 * Set the repository instance on the composite loader.
	 *
	 * @param  Illuminate\Config\Repository  $repository
	 * @return void
	 */
	public function setRepository(Repository $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * Sets the database table used by the
	 * loaded.
	 *
	 * @param  string  $databaseTable
	 */
	public function setDatabaseTable($databaseTable)
	{
		$this->databaseTable = $databaseTable;
	}

	/**
	 * Cache all configurations.
	 *
	 * @return void
	 */
	public function cacheConfigs()
	{
		$configs = $this->database->table($this->databaseTable)->get(); //->rememberForever('laradic.config')->get();

		$cachedConfigs = array();

		foreach ($configs as $key => $config)
		{
			$cachedConfigs["{$config->environment}.{$config->namespace}.{$config->group}"][$config->item] = $config;

            $k = '';
            if($config->namespace){
                $k .= $config->namespace . '::';
            }

            $k .= $config->group . '.' . $config->item;
           # $nc[$k]

            $this->repository->set($k, $config->value);
		}

		$this->cachedConfigs = $cachedConfigs;


	}

	/**
	 * Returns a query builder object for the given environment, group
	 * and namespace.
	 *
	 * @param  string  $environment
	 * @param  string  $group
	 * @param  string  $namespace
	 * @param  string  $fallback
	 * @return Illuminate\Database\Query  $query
	 */
	protected function getGroupQuery($environment, $group, $namespace, $fallback = true)
	{
		$query = $this->database->table($this->databaseTable);

		if ($fallback === true)
		{
			$query->whereNested(function($query) use ($environment)
			{
				$query->where('environment', '=', '*');

				if ($environment != '*')
				{
					$query->orWhere('environment', '=', $environment);
				}
			});
		}
		else
		{
			$query->where('environment', '=', $environment);
		}

		$query->where('group', '=', $group);

		if (isset($namespace))
		{
			$query->where('namespace', '=', $namespace);
		}
		else
		{
			$query->whereNull('namespace');
		}

		if ($fallback === true)
		{
			$query->orderBy('environment');
		}

		return $query;
	}

	/**
	 * Parses a value from the database and attempts to return it's
	 * JSON decoded value.
	 *
	 * @param  string  $json
	 * @return mixed
	 */
	protected function parseValue($value)
	{
		$decoded = json_decode($value, true);

		if (json_last_error() !== JSON_ERROR_NONE) return $value;

		return $decoded;
	}

	/**
	 * Prepares a value to be persisted in the database.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function prepareValue($value)
	{
		// We will always JSON encode the value. This allows us to store "null", "true"
		// and "false" values in the database (as an example), which may mean completely
		// different things.
		return json_encode($value);
	}

	/**
	 * Removes the repository cache for the given item.
	 *
	 * @param  string  $group
	 * @param  string  $item
	 * @param  string  $namespace
	 * @return void
	 */
	protected function removeRepositoryCache($group, $item, $namespace = null)
	{
		// If the repository exists, we'll
		if ( ! isset($this->repository)) return;

		$key = "{$group}.{$item}";

		if (isset($namespace))
		{
			$key = "{$namespace}::{$key}";
		}

		$this->repository->set($key, null);

		// Purge cached configurations
		#$this->database->getCacheManager()->forget('laradic.config');
	}

	/**
	 * Returns a cache key for the given environment,
	 * group and namespace. Used when overriding cascaded
	 * file configuration in the database.
	 *
	 * @param  string  $environment
	 * @param  string  $group
	 * @param  string  $namespace
	 * @return string
	 */
	protected function getCacheKey($environment, $group, $namespace = null)
	{
		return implode('.', func_get_args());
	}

}
