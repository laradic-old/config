<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 */
namespace Laradic\Config\Loaders;


use Illuminate\Database\ConnectionInterface;
use Laradic\Config\Repository;

/**
 * Class DatabaseLoader
 *
 * @package     Laradic\Config
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 */
class DatabaseLoader extends FileLoader implements LoaderInterface
{

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

    /** {@inheritDoc} */
    public function set($key, $value = null, $environment = null)
    {
        if ( ! isset($this->repository) )
        {
            throw new \RuntimeException("Repository is required to set a config value. Use persist() instead.");
        }

        list($namespace, $group, $item) = $this->repository->parseKey($key);
        $environment = $environment ? $environment : $this->repository->getEnvironment();

        $this->persist($environment, $group, $item, $value, $namespace);
    }

    /**
     * Persist the given configuration to the database.
     *
     * @param  string $environment
     * @param  string $group
     * @param  string $name
     * @param  mixed $value
     * @param  string $namespace
     * @return void
     */
    public function persist($environment, $group, $item, $value = null, $namespace = null)
    {
        // If there is no databse, we'll not persist anything which will make
        // the configuration act as if this package was not installed.
        if ( ! isset($this->database) )
        {
            return;
        }

        $query = $this
            ->getGroupQuery($environment, $group, $namespace, false)
            ->where('item', '=', $item);

        // Firstly, we'll see if the configuration exists
        $existing = $query->first();

        if ( $existing )
        {
            if ( isset($value) )
            {
                // We'll update an existing record
                $query->update(array('value' => $this->prepareValue($value)));
            }
            else
            {
                $query->delete();
            }
        }
        elseif ( isset($value) )
        {
            // Prepare our data
            $data          = compact('environment', 'group', 'item');
            $data['value'] = $this->prepareValue($value);
            if ( isset($namespace) )
            {
                $data['namespace'] = $namespace;
            }

            $this
                ->database->table($this->databaseTable)
                ->insert($data);
        }
    }

    /**
     * Returns the database connection.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Sets the database connection.
     *
     * @param  Illuminate\Database\Connection $database
     * @return void
     */
    public function setDatabase(ConnectionInterface $database)
    {
        $this->database = $database;
    }

    /**
     * Set the repository instance on the composite loader.
     *
     * @param  Illuminate\Config\Repository $repository
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
     * @param  string $databaseTable
     */
    public function setDatabaseTable($databaseTable)
    {
        $this->databaseTable = $databaseTable;
        $configs             = $this->database->table($databaseTable)->get();

        foreach ($configs as $key => $config)
        {
            $k = '';
            if ( $config->namespace )
            {
                $k .= $config->namespace . '::';
            }
            $k .= $config->group . '.' . $config->item;
            $this->repository->set($k, $config->value);
        }
    }

    /**
     * Returns a query builder object for the given environment, group
     * and namespace.
     *
     * @param  string $environment
     * @param  string $group
     * @param  string $namespace
     * @param  string $fallback
     * @return \Illuminate\Database\Query  $query
     */
    protected function getGroupQuery($environment, $group, $namespace, $fallback = true)
    {
        $query = $this->database->table($this->databaseTable);

        if ( $fallback === true )
        {
            $query->whereNested(function ($query) use ($environment)
            {
                $query->where('environment', '=', '*');

                if ( $environment != '*' )
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

        if ( isset($namespace) )
        {
            $query->where('namespace', '=', $namespace);
        }
        else
        {
            $query->whereNull('namespace');
        }

        if ( $fallback === true )
        {
            $query->orderBy('environment');
        }

        return $query;
    }

    /**
     * Parses a value from the database and attempts to return it's
     * JSON decoded value.
     *
     * @param  string $json
     * @return mixed
     */
    protected function parseValue($value)
    {
        $decoded = json_decode($value, true);

        if ( json_last_error() !== JSON_ERROR_NONE )
        {
            return $value;
        }

        return $decoded;
    }

    /**
     * Prepares a value to be persisted in the database.
     *
     * @param  mixed $value
     * @return mixed
     */
    protected function prepareValue($value)
    {
        // We will always JSON encode the value. This allows us to store "null", "true"
        // and "false" values in the database (as an example), which may mean completely
        // different things.
        return json_encode($value);
    }
}
