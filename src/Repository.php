<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 */
namespace Laradic\Config;

use ArrayAccess;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Filesystem\Filesystem;
use Laradic\Support\Arrays;
use Laradic\Config\Contracts\PackageRepository;
use Laradic\Config\Loaders\LoaderInterface;
use Laradic\Config\Traits\CascadingTrait;
use Laradic\Config\Traits\LoadingTrait;
use Laradic\Support\Traits\NamespacedItemResolverTrait;

/**
 * Class Repository
 *
 * @package     Laradic\Config
 * @author      Robin Radic
 * @author      Mior Muhammad Zaki
 * @author      Taylor Otwell
 * @license     MIT
 * @copyright   Check the embedded LICENSE file
 */
class Repository extends \Illuminate\Config\Repository implements ArrayAccess, ConfigContract, PackageRepository
{
    use CascadingTrait, LoadingTrait;

    use NamespacedItemResolverTrait
    {
        parseNamespacedSegments as parentParseNamespacedSegments;
        parseBasicSegments as parentParseBasicSegments;
    }

    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * All of the registered packages.
     *
     * @var array
     */
    protected $packages = [];

    /** @var \Laradic\Config\Publisher[] */
    protected $publishers = [];

    /** @var \Illuminate\Filesystem\Filesystem  */
    protected $files;

    /**
     * Create a new configuration repository.
     *
     * @param \Laradic\Config\Loaders\LoaderInterface $loader
     * @param \Illuminate\Filesystem\Filesystem       $files
     * @param  string                                 $environment
     */
    public function __construct(LoaderInterface $loader, Filesystem $files, $environment)
    {

        $this->setLoader($loader);
        $this->files = $files;
        $this->environment = $environment;
    }

    public function addPublisher($package, $sourcePath)
    {
        $this->publishers[$package] = Publisher::create($this->files)
            ->package($package)
            ->from($sourcePath);
    }

    public function getPublishers()
    {
        return $this->publishers;
    }

    public function publish($package = null)
    {
        if(is_null($package))
        {
            foreach($this->getPublishers() as $publisher)
            {
                $publisher->publish();
            }
        }
        else
        {
            if(!isset($this->publishers[$package]))
            {
                throw new \InvalidArgumentException("Config publisher [$package] does not exist");
            }
            $this->publishers[$package]->publish();
        }
    }


    /**
     * Determine if the given configuration value exists.
     *
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        $default = microtime(true);

        return $this->get($key, $default) !== $default;
    }

    /**
     * Determine if a configuration group exists.
     *
     * @param  string $key
     * @return bool
     */
    public function hasGroup($key)
    {
        list($namespace, $group) = $this->parseKey($key);

        return $this->loader->exists($group, $namespace);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        list($namespace, $group, $item) = $this->parseKey($key);

        // Configuration items are actually keyed by "collection", which is simply a
        // combination of each namespace and groups, which allows a unique way to
        // identify the arrays of configuration items for the particular files.
        $collection = $this->getCollection($group, $namespace);

        $this->load($group, $namespace, $collection);

        return Arrays::get($this->items[$collection], $item, $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value = null)
    {
        if ( is_array($key) )
        {
            return $this->setItems($key);
        }

        list($namespace, $group, $item) = $this->parseKey($key);

        $collection = $this->getCollection($group, $namespace);

        // We'll need to go ahead and lazy load each configuration groups even when
        // we're just setting a configuration item so that the set item does not
        // get overwritten if a different item in the group is requested later.
        $this->load($group, $namespace, $collection);

        if ( is_null($item) )
        {
            $this->items[$collection] = $value;
        }
        else
        {
            Arrays::set($this->items[$collection], $item, $value);
        }
    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $config = $this->get($key);

        $this->set($key, array_unshift($config, $value));
    }

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function push($key, $value)
    {
        $config = $this->get($key);

        $this->set($key, array_push($config, $value));
    }

    /**
     * Set a given collections of configuration value.
     *
     * @param  array $items
     * @return Repository
     */
    protected function setItems(array $items)
    {
        foreach ($items as $key => $value)
        {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Load the configuration group for the key.
     *
     * @param  string $group
     * @param  string $namespace
     * @param  string $collection
     * @return void
     */
    protected function load($group, $namespace, $collection)
    {
        $env = $this->environment;

        // If we've already loaded this collection, we will just bail out since we do
        // not want to load it again. Once items are loaded a first time they will
        // stay kept in memory within this class and not loaded from disk again.
        if ( isset($this->items[$collection]) )
        {
            return;
        }

        $items = $this->loader->load($env, $group, $namespace);

        // If we've already loaded this collection, we will just bail out since we do
        // not want to load it again. Once items are loaded a first time they will
        // stay kept in memory within this class and not loaded from disk again.
        if ( isset($this->afterLoad[$namespace]) )
        {
            $items = $this->callAfterLoad($namespace, $group, $items);
        }

        $this->items[$collection] = $items;
    }

    /**
     * Parse an array of namespaced segments.
     *
     * @param  string $key
     * @return array
     */
    protected function parseNamespacedSegments($key)
    {
        list($namespace, $item) = explode('::', $key);

        // If the namespace is registered as a package, we will just assume the group
        // is equal to the namespace since all packages cascade in this way having
        // a single file per package, otherwise we'll just parse them as normal.
        if ( in_array($namespace, $this->packages) )
        {
            return $this->parsePackageSegments($key, $namespace, $item);
        }

        return $this->parentParseNamespacedSegments($key);
    }

    /**
     * Parse the segments of a package namespace.
     *
     * @param  string $key
     * @param  string $namespace
     * @param  string $item
     * @return array
     */
    protected function parsePackageSegments($key, $namespace, $item)
    {
        $itemSegments = explode('.', $item);

        // If the configuration file doesn't exist for the given package group we can
        // assume that we should implicitly use the config file matching the name
        // of the namespace. Generally packages should use one type or another.
        if ( ! $this->loader->exists($itemSegments[0], $namespace) )
        {
            return [$namespace, 'config', $item];
        }

        return $this->parentParseNamespacedSegments($key);
    }

    /**
     * Register a package for cascading configuration.
     *
     * @param  string $package
     * @param  string $hint
     * @param  string $namespace
     * @return void
     */
    public function package($package, $hint, $namespace = null)
    {
        $namespace = $this->getPackageNamespace($package, $namespace);

        $this->packages[] = $namespace;

        // First we will simply register the namespace with the repository so that it
        // can be loaded. Once we have done that we'll register an after namespace
        // callback so that we can cascade an application package configuration.
        $this->addNamespace($namespace, $hint);

        $this->afterLoading($namespace, function (Repository $me, $group, $items) use ($package)
        {
            $env = $me->getEnvironment();

            $loader = $me->getLoader();

            return $loader->cascadePackage($env, $package, $group, $items);
        });
    }

    /**
     * Get the configuration namespace for a package.
     *
     * @param  string $package
     * @param  string $namespace
     * @return string
     */
    protected function getPackageNamespace($package, $namespace)
    {
        if ( is_null($namespace) )
        {
            list(, $namespace) = explode('/', $package);
        }

        return $namespace;
    }

    /**
     * Get the collection identifier.
     *
     * @param  string $group
     * @param  string $namespace
     * @return string
     */
    protected function getCollection($group, $namespace = null)
    {
        $namespace = $namespace ?: '*';

        return $namespace . '::' . $group;
    }

    /**
     * Get all of the configuration items.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }
}
