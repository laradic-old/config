<?php
 /**
 * Part of the Radic packages. 
 */

return array(
    'connection' => 'array',
    'connections' => array(
        'array' => array(
            'driver' => 'Laradic\Config\Drivers\ArrayDriver',
            'destination' => storage_path('laradic/config/array')
        ),
        'yaml' => array(
            'driver' => 'Laradic\Config\Drivers\YamlDriver',
            'destination' => storage_path('laradic/config/yaml')
        ),
        'db' => array(
            'driver' => 'Laradic\Config\Drivers\DatabaseDriver',
            'destination' => 'laradic_config' # Table name
        )
    )
);