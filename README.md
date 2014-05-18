ConfigLoader [![Build Status](https://travis-ci.org/JimmDiGrizli/phalcon-config-loader.png?branch=develop)](https://travis-ci.org/JimmDiGrizli/phalcon-config-loader)
===============================

ConfigLoader - it's manager configuration files for Phalcon. It allows you to create a configuration of various formats (ini, yaml, JSON, or any other, for which you will add adapter) via a single method. 

```php
$configYml = $configLoader->create('config.yml');
$configIni = $configLoader->create('config.ini');
```

ConfigLoader is able to track ```%environment%``` in configuration files and replace it on our environment.

```php
// Create ConfigLoader and specify the environment of our application
$configLoader = new ConfigLoader('prod');

// config.yml : test: %environment%
$configYml = $configLoader->create('config.yml');

echo $configYml->test;
// print: prod 
```
To add your adapter, you must call ```add ()``` with transfer expansion and adapter class, 
which must inherit a class ```Phalcon\Config```:

```php
$config = $configLoader->add('xml', 'MyNamespace/XmlConfig');
```

Moreover, you can merge configuration files:


```ini
#config.ini
[test]
test = true
%res% = import.ini
exp = %res:import.ini
%class% = Namespace/Class::SERVICES
import-class =  %class:Namespace/Class::SERVICES

```

```ini
#import.ini
import = "test"
```

```php
namespace Namespace;

class Class {
  const SERVICES = '/const.ini';
}
```


```ini
#const.ini
class = "class"
```


The result loading configuration from ```config.ini```:

```php
[                               
    'test' => [                 
        'test' => true,                             
        'import' => true,       
        'env' => 'dev',
        'exp' => [
            'import' => true,
            'env' => 'dev'
        ],
        'class' => "class",
        'impot-class' => [
            'import-class' => "class"
        ]
    ]                           
]                               
```

Declared variables in the parent file will not be replaced by variables from the child (only %res% or %class%):

```ini
# /app/config/config.ini
%res% = include.ini
[foo]
test = config-test
```

```ini
# /app/config/include.ini
[foo]
test = test
bar = bar
```

```ini
# result
[foo]
test = config-test
bar = bar
```

If you do not want to import resources (loading of the other configuration files in this configuration), the second parameter must pass a boolean ```false ```:

```php
$config = $configLoader->create('config.ini', false);
```

