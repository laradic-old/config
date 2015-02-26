<?php namespace Laradic\Config\Traits;

use Laradic\Config\LoaderInterface;

trait CascadingTrait
{
    /**
     * The loader implementation.
     *
     * @var \Laradic\Config\LoaderInterface
     */
    protected $loader;

    /**
     * The current environment.
     *
     * @var string
     */
    protected $environment;

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    public function addNamespace($namespace, $hint)
    {
        $this->loader->addNamespace($namespace, $hint);
    }

    /**
     * Returns all registered namespaces with the config
     * loader.
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->loader->getNamespaces();
    }

    /**
     * Get the loader implementation.
     *
     * @return \Illuminate\Config\LoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Set the loader implementation.
     *
     * @param  \Laradic\Config\LoaderInterface  $loader
     * @return void
     */
    public function setLoader(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Get the current configuration environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
