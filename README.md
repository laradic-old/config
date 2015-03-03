Laradic Config
===================================

[![Build Status](https://travis-ci.org/RobinRadic/testing.svg?branch=master)](https://travis-ci.org/RobinRadic/testing)
[![GitHub version](https://badge.fury.io/gh/robinradic%2Ftesting.svg)](http://badge.fury.io/gh/robinradic%2Ftesting)
[![Total Downloads](https://poser.pugx.org/radic/testing/downloads.svg)](https://packagist.org/packages/radic/testing)
[![Goto documentation](http://img.shields.io/badge/goto-documentation-orange.svg)](http://docs.radic.nl/testing)
[![Goto repository](http://img.shields.io/badge/goto-repository-orange.svg)](https://github.com/robinradic/testing)
[![License](http://img.shields.io/badge/license-MIT-blue.svg)](http://radic.mit-license.org)

Development build
-----------

**Laravel 5** package providing several classes and traits to help out with unit-tests for laravel packages.

#### Installation  
###### Requirements
```JSON
"PHP": ">=5.4.0"
```
  
###### Composer
```JSON
"laradic/debug": "dev-master"
```


#### Some examples
```php
Config::set('myconfig.path', base_path('test'));
Config::save('my-connection');
Config::save('db');
Config::save('yml');
Config::save('array');
Config::save(array(
    'driver' => 'array',
    'path' => storage_path('laradic/config')
));

// Then another time
Config::get('myconfig.path'); //output> path/to/base/test

// Namespaces
Config::get('myvendor/mypackage::mykey')
Config::get('myvendor/mypackage::configfile2.mykey2')
```

### Copyright/License
Copyright 2015 [Robin Radic](https://github.com/RobinRadic) - [MIT Licensed](http://radic.mit-license.org)
