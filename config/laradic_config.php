<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 */
return array(
    /*
     * The loader that should be used to load the configuration items
     * loaders:
     *      file:   The standard file loader. enables saving/loading from files.
     *              Saved config items will be placed in the storage path. It will use the following priority chain:
     *              saved config item > published config item > non-published config item
     *
     *      db:     The database loader. Extends the file loader, enables saving/loading from database.
     *              If a config item doesn't exist in the DB table, it will fallback to the file loader
     */
    'loader' => 'file',

    /*
     *
     */
    'loaders' => array(
        'file' => array(
            'save_path' => storage_path('laradic_config')
        ),
        'db' => array(
            'table' => 'config'
        )
    ),

    /*
     * Register the ConsoleServiceProvider to add additional console commands
     * commands: ...
     */
    'console' => false
);
