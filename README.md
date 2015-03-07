<!---
title: Overview
author: Robin Radic
icon: fa fa-eye
-->
Laravel Config Extension
===================================
<!--- [![Code Coverage](https://img.shields.io/badge/coverage-100%-green.svg?style=flat-square)](http://robin.radic.nl/config/coverage) -->
<!--- [![Goto API Documentation](https://img.shields.io/badge/goto-api--docs-orange.svg?style=flat-square)](http://robin.radic.nl/config/api) -->
[![Build Status](https://img.shields.io/travis/laradic/config.svg?branch=master&style=flat-square)](https://travis-ci.org/laradic/config)
[![GitHub Version](https://img.shields.io/github/tag/laradic/config.svg?style=flat-square&label=version)](http://badge.fury.io/gh/laradic%2Fconfig)
[![Total Downloads](https://img.shields.io/packagist/dt/laradic/config.svg?style=flat-square)](https://packagist.org/packages/laradic/config)
[![License](http://img.shields.io/badge/license-MIT-ff69b4.svg?style=flat-square)](http://radic.mit-license.org)
  
[![Goto Documentation](http://img.shields.io/badge/@-documentation-orange.svg?style=flat-square)](http://docs.radic.nl/config)
[![Goto API Documentation](http://img.shields.io/badge/@-api-orange.svg?style=flat-square)](http://docs.radic.nl/config)
[![Goto Repository](http://img.shields.io/badge/@-repository-orange.svg?style=flat-square)](https://github.com/laradic/config)
  
  
Overview
-----------
**Laravel 5** package providing extra configuration features like file/db saving and namespaces.

##### Feature highlights
- Works out of the box, no need to alter configuration files or directory structures
- Use namespaces, like laravel 4 enabled you to do
- Persistent config, you can save config values (doesn't override any files)
- Multiple persistent config storage methods.
- Database storage method
- File based storage method

##### Small usage example 
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
  
Documentation
-----------
Check the [documentation](http://docs.radic.nl/laradic-config) for (way) more information
  
  
Copyright/License
-----------
Copyright 2015 [Robin Radic](https://github.com/RobinRadic) - [MIT Licensed](http://radic.mit-license.org)
