<?php 
 /**
 * Part of the Radic packages. 
 */
namespace Laradic\Config\Contracts;
/**
 * Class PackageRepository
 *
 * @package     Radic\Config\Contracts
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 */

interface PackageRepository
{
    /**
     * Register a package for cascading configuration.
     *
     * @param  string  $package
     * @param  string  $hint
     * @param  string  $namespace
     * @return void
     */
    public function package($package, $hint, $namespace = null);
}