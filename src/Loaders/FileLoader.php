<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 */
namespace Laradic\Config\Loaders;

use Illuminate\Filesystem\Filesystem;
use Laradic\Config\Repository;
use Laradic\Support\Arrays;
use Laradic\Support\Path;
use Laradic\Support\String;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FileLoader
 *
 * @package     Laradic\Config
 * @author      Robin Radic
 * @author      Mior Muhammad Zaki
 * @author      Taylor Otwell
 * @license     MIT
 * @copyright   Check the embedded LICENSE file
 */
class FileLoader implements LoaderInterface
{

    /**
     * The config repository instance.
     *
     * @var \Laradic\Config\Repository
     */
    protected $repository;

    /**
     * This package it's configuration
     * @var array
     */
    protected $laradicConfig;

    /**
     * The filesystem instance.
     *
     * @var \Laradic\Support\Filesystem
     */
    protected $files;

    /**
     * The default configuration path.
     *
     * @var string
     */
    protected $defaultPath;

    /**
     * All of the named path hints.
     *
     * @var array
     */
    protected $hints = [];

    /**
     * A cache of whether vendor and groups exists.
     *
     * @var array
     */
    protected $exists = [];

    /**
     * Create a new file configuration loader.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $defaultPath
     */
    public function __construct(Filesystem $files, $defaultPath)
    {
        $this->files = $files;
        $this->defaultPath = $defaultPath;
        #$this->laradicConfig = array_dot($files->getRequire(Path::join($defaultPath, 'laradic_config.php')));
    }

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
            throw new \RuntimeException('Repo is needed to set config.');
        }

        list($namespace, $group, $item) = $this->repository->parseKey($key);
        $environment = $environment ? $environment : $this->repository->getEnvironment();

        $path = String::remove($this->getPath($namespace), Path::canonicalize(base_path()));

        $parts = "{$environment} - {$namespace} - {$group} - {$item} - {$value}";

        $f = $this->files;
        $storPath = $this->laradicConfig['loaders.file.save_path'];

        $fileName = "global.php";
        if($namespace){
            $fileName = (string) \Stringy\Stringy::create($namespace)->slugify()->ensureRight('.php');
        }
        $file = path_join($storPath, $fileName);
        $items = $f->exists($file) ? $f->getRequire($file) : [];
        $dest = "{$environment}.{$group}" . (isset($item) ? ".{$item}" : '');
        array_set($items, $dest, $value);
        $this->files->put($file, "<?php \n return " . var_export($items, true) . ';');



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
        $items = [];
        $path = $this->getPath($namespace);

        if (is_null($path)) {
            return $items;
        }

        # 1
        $file = "{$path}/{$group}";
        if ($this->files->exists("{$file}.php")) {
            $items = $this->getRequire("{$file}.php");
        } elseif($this->files->exists("{$file}.yml")) {
            $items = $this->getYaml("{$file}.yml");
        }

        # 2
        $file = "{$path}/{$environment}/{$group}";
        if ($this->files->exists("{$file}.php")) {
            $items = array_replace_recursive($items, $this->getRequire("{$file}.php"));
        } elseif ($this->files->exists("{$file}.yml")) {
            $items = array_replace_recursive($items, $this->getYaml("{$file}.yml"));
        }


        if($namespace === null){

            $f = $this->files;
            $storPath = $this->laradicConfig['loaders.file.save_path'];
            $file = Path::join($storPath, 'global.php');
            if($f->exists($file)){
                $my_items = $f->getRequire($file);
                $my_items = array_get($my_items, "{$environment}.{$group}");
                if($my_items)
                {
                    $items = array_replace_recursive($items, $my_items);
                }
            }

        }

        return $items;
    }



    /**
     * Determine if the given group exists.
     *
     * @param  string  $group
     * @param  string  $namespace
     * @return bool
     */
    public function exists($group, $namespace = null)
    {
        $key = $group.$namespace;

        if (! isset($this->exists[$key])) {

            $path = $this->getPath($namespace);

            if (is_null($path)) {
                return $this->exists[$key] = false;
            }

            $file = "{$path}/{$group}";


            $this->exists[$key] = false;

            if($this->files->exists("{$file}.php"))
            {
                $this->exists[$key] = true;
            }
            if($this->files->exists("{$file}.yml"))
            {
                $this->exists[$key] = true;
            }

        }

        return $this->exists[$key];
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

        $file = "packages/{$package}/{$group}";

        if ($this->files->exists($path = $this->defaultPath.'/'.$file.'.php')) {
            $items = array_merge($items, $this->getRequire($path));
        } elseif ($this->files->exists($path = $this->defaultPath.'/'.$file.'.yml')) {
            $items = array_merge($items, $this->getYaml($path));
        }

        // @todo to yaml
        $path = $this->getPackagePath($env, $package, $group);

        if ($this->files->exists("{$path}.php")) {
            $items = array_merge($items, $this->getRequire("{$path}.php"));
        } elseif ($this->files->exists("{$path}.yml")) {
            $items = array_merge($items, $this->getYaml("{$path}.yml"));
        }

        $f = $this->files;
        $storPath = $this->laradicConfig['loaders.file.save_path'];
        $fileName = (string) \Stringy\Stringy::create($package)->slugify()->ensureRight('.php');
        $file = Path::join($storPath, $fileName);
        if($f->exists($file)){
            $my_items = $f->getRequire($file);
            $my_items = array_get($my_items, "{$env}.{$group}");
            if($my_items)
            {
                $items = array_replace_recursive($items, $my_items);
            }
        }

        return $items;
    }

    /**
     * Get the package path for an environment and group.
     *
     * @param  string  $env
     * @param  string  $package
     * @param  string  $group
     * @return string
     */
    protected function getPackagePath($env, $package, $group)
    {
        $file = "packages/{$package}/{$env}/{$group}";

        return $this->defaultPath.'/'.$file;
    }

    /**
     * Get the configuration path for a namespace.
     *
     * @param  string  $namespace
     * @return string
     */
    protected function getPath($namespace)
    {
        if (is_null($namespace)) {
            return $this->defaultPath;
        } elseif (isset($this->hints[$namespace])) {
            return $this->hints[$namespace];
        }
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    public function addNamespace($namespace, $hint)
    {
        $this->hints[$namespace] = $hint;
    }

    /**
     * Returns all registered vendor with the config
     * loader.
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->hints;
    }

    /**
     * Get a file's contents by requiring it.
     *
     * @param  string  $path
     * @return mixed
     */
    protected function getRequire($path)
    {
        return $this->files->getRequire($path);
    }


    /**
     * Get a YAML file's parsed content
     *
     * @param  string  $path
     * @return array
     */
    protected function getYaml($path)
    {
        return Yaml::parse($this->files->get($path));
    }

    /**
     * Get the Filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Set the repository instance on the composite loader.
     *
     * @param  \Illuminate\Config\Repository  $repository
     * @return void
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
        $this->laradicConfig = array_dot($repository->get('laradic_config'));
    }
}
