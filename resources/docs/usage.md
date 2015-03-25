<!---
title: Usage
author: Robin Radic
icon: fa fa-legal
-->

#### Define the configuration 
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
 
#### Usage
###### Getting configuration
You can get configuration values like this:

```php
Config::get('package.config.item');
Config::get('vendorname/packagename::config.item')
```

###### Setting persistent configuration
You can set persistent config values like this:

```php
Config::getLoader()->set('config.item', 'value');
```
