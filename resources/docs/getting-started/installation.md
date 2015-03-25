<!---
title: Installation
author: Robin Radic
icon: fa fa-legal
-->

###### Composer
```php
"laradic/config": "1.*"
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

