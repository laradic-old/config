<?php
 /**
 * Part of the Radic packages.
 */
namespace Laradic\Config;

use Illuminate\Filesystem\Filesystem;

/**
 * Class Publisher
 *
 * @package     Laradic\Themes
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 */
class Publisher
{
    protected $package;

    protected $sourcePath;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /**
 * Instanciates the class
 */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function publish()
    {
        $destination = config_path('packages/' . $this->package);
        if(!$this->files->exists($this->sourcePath))
        {
            return;
        }
        if(!$this->files->exists($destination))
        {
            $this->files->makeDirectory($destination, 0755, true);
        }

        $this->files->copyDirectory($this->sourcePath, $destination);
    }

    public static function create(Filesystem $files)
    {
        return new static($files);
    }

    public function package($package)
    {
        $this->package = $package;
        return $this;
    }

    public function from($sourcePath)
    {
        $this->sourcePath = $sourcePath;
        return $this;
    }

}
