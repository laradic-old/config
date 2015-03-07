<!---
title: Overview
author: Robin Radic
icon: fa fa-eye
-->
  
Laravel Config Extension
========================
  
  
  
### Feature highlights
- Works out of the box, no need to alter configuration files or directory structures
- Use namespaces, like laravel 4 enabled you to do
- Persistent config, you can save config values (doesn't override any files)
- Multiple persistent config storage methods.
- Database storage method
- File based storage method

##### Quick overview
```php
//
// Normal behaviour like always
Config::get('propackage.foo.bar'); //> foobar
Config::set('propackage.foo.bar', 'I dont like foo and bars');
Config::get('propackage.foo.bar'); //> I dont like foo and bars

# Restarting app / refreshing page
Config::get('propackage.foo.bar'); //> foobar

//
// Using namespaces
Config::get('joshua/noob::foo.bar'); //> foobar
Config::set('joshua/noob::foo.bar', 'I dont like foo and bars'); 
Config::get('joshua/noob::foo.bar'); //> I dont like foo and bars

# Restarting app / refreshing page
Config::get('joshua/noob::foo.bar'); //> foobar

//
// Persistent configuration. Lets save some foo.
Config::getLoader()->set('joshua/noob::foo.bar', 'I dont like foo and bars'); 
Config::getLoader()->set('propackage.foo.bar', 'I dont like foo and bars'); //> foobar

# Restarting app / refreshing page
Config::get('propackage.foo.bar'); //> I dont like foo and bars
Config::get('joshua/noob::foo.bar'); //> I dont like foo and bars
```


##### Changing storage method
First publish the config file for this package. This will publish the config file and migration (for db storage) file
```bash
php artisan vendor:publish
# or if you'd rather not have the migration file published:
php artisan vendor:publish --tag="config"
```
Edit the `laradic_config.php` file and change the `loader` key. Currently only `file` and `db` are supported. 
```php
return array(
    'loader' => 'file', // change to 'db' for database storage
    //...
);
```

##### Documentation
Check the documentation for (way) more information


