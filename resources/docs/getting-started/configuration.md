<!---
title: Configuration
author: Robin Radic
icon: fa fa-legal
-->

#### Loader
You can define the loader as follows. Currently the only loaders are `file` and `db`. The loader will load persistent config values.

```php
return array(
    'loader' => 'file',
    //....
);
```

#### Loader configuration
You can set persistent config items, by default the values will be saved in a seperate file that gets merged. It is also possible to save to database.

```php
return array(
    'loaders' => array(
        'file' => array(
            'save_path' => storage_path('laradic_config')
        ),
        'db' => array(
            'table' => 'config'
        )
    ),
    //.... other options
);
```
**Important**: If you plan on using the `db` loader, you will have to run the included migration that will create the required `config` database table.


#### Console commands
If you want to use the provided console commands, you need to enable it:
```php
return array(
    // ....
    'console' => true,
    // ....
);
```

