<a name="top"></a>![Laravel logo](http://laravel.com/assets/img/laravel-logo.png) Laravel 5 Config package
============================

[![GitHub Version](https://img.shields.io/github/tag/laradic/config.svg?style=flat-square&label=version)](http://badge.fury.io/gh/laradic%2Fconfig)
[![Total Downloads](https://img.shields.io/packagist/dt/laradic/config.svg?style=flat-square)](https://packagist.org/packages/laradic/config)
[![License](http://img.shields.io/badge/license-MIT-ff69b4.svg?style=flat-square)](http://radic.mit-license.org)

## Version 1.3

### Features
- Namespaced config (like laravel 4: `Config::get('vendor/package::config.item')`)
- Namespaced publishing (like laravel 4: `config/packages/VENDOR/PACKAGE/config.php`)
- Or use the standard Laravel 5 way:     Compatible with laravel 5 default configs. Adding the package will not invalidate your current setup. 
- Persistent configuration. Save changes to a **`mirroring` `file` or `database`**.
- `Config::getLoader()->set('iam/awesome::my.config.key', 'A changed value')` saves it to a **mirroring** `file` or `db`
- Supports **PHP**, **YAML** and soon also **XML** configuration files.

  
-----------
  
<a name="overview"></a>
### Overview <sub>[^](#top)</sub>
- [Features](#top)
- [Overview](#overview)
- [Installation](#installation)
- [Usage](#usage)
- [Persistent config](#persistent)
- [Todo](#todo)
- [Copyright/license](#copyright)
  
-----------


<a name="installation"></a>
### Installation <sub>[^](#top)</sub>

###### Composer
```php
"laradic/config": "1.3.*"
```

###### Service provider
```php
"Laradic\Config\ConfigServiceProvider"
```

###### Bootstrapper
Replace the default laravel `Illuminate\Foundation\Bootstrap\LoadConfiguration` bootstrapper
with `Laradic\Config\Bootstrap\LoadConfiguration` bootstrapper inside `app/Http/Kernel.php` and `app/Console/Kernel.php`. 

```php
use Illuminate\Foundation\Http\Kernel as HttpKernel;
class Kernel extends HttpKernel {
    protected $bootstrappers = [
        'Illuminate\Foundation\Bootstrap\DetectEnvironment',
        'Laradic\Config\Bootstrap\LoadConfiguration',
        'Illuminate\Foundation\Bootstrap\ConfigureLogging',
        'Illuminate\Foundation\Bootstrap\HandleExceptions',
        'Illuminate\Foundation\Bootstrap\RegisterFacades',
        'Illuminate\Foundation\Bootstrap\RegisterProviders',
        'Illuminate\Foundation\Bootstrap\BootProviders',
    ];
}
```
  
  
<a name="usage"></a>
### Basic usage <sub>[^](#top)</sub>
Inside any ServiceProvider:

```php
class YourServiceProvider extends ServiceProvider
{
    use ConfigProviderTrait;

    public function register()
    {
        $this->addConfigComponent('vendorname/packagename', 'vendorname/packagename', realpath(__DIR__.'/../resources/config'));    
    }
}
```
- Namespaced configuration can be accessed with `Config::get('vendorname/packagename::config.item')`. 
- Publishing the config file is done with the default laravel `vendor:publish` command.
  
  
  
<a name="persistent"></a>
### Persistent config <sub>[^](#top)</sub>
You can set persistent config items, by default the values will be saved in a seperate, mirrored file that gets merged on boot. 
It is also possible to save to database.

Inside the config file you can change the save method by changing the [`loader`] value.

```php
return array(
    'loader' => 'file',
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

You can set persistent config values like this:

```php
Config::getLoader()->set('config.item', 'value');
```
  
  
  
<a name="todo"></a>
### Todo <sub>[^](#top)</sub>
- [x] YAML/YML file support.
- [x] Database saving
- [ ] XML file support
- [ ] Unit tests
  
  
<a name="copyright"></a>
### Copyright/License <sub>[^](#top)</sub>
Copyright 2015 [Robin Radic](https://github.com/RobinRadic) - [MIT Licensed](http://radic.mit-license.org)
