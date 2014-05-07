ConfigLoader [![Build Status](https://travis-ci.org/JimmDiGrizli/phalcon-config-loader.png?branch=develop)](https://travis-ci.org/JimmDiGrizli/phalcon-config-loader)
===============================

ConfigLoader - it's manager of configuration files for Phalcon. It allows you to create a configuration of various formats (ini, yaml, JSON, or any other, for which you will add adapter) via a single method. 

```php
$config_yml = $configloader->create('config.yml');
$config_ini = $configloader->create('config.ini');
```

ConfigLoader is able to track ```%environment%``` in configuration files and replace it on our environment.

```php
// Create ConfigLoader and specify the environment of our application
$configloader = new ConfigLoader('prod');

// config.yml : test: %environment%
$config_yml = $configloader->create('config.yml');

echo $config_yml->test;
// print: prod 
```
To add your adapter, you must call ```add ()``` with the transfer expansion and adapter class, 
which must inherit a class ```Phalcon\Config```:

```php
$config = $configLoader->add('xml', 'MyNamespace/XmlConfig');
```

Moreover, you can import the configuration files in some others:

```ini
# config.ini
  
# Path from configuration file
%res% = 'config.yml
import-file = $res:config.yml
  
# Path from class's constant of class
%class% = Namespace/Class::SERVICES
import-class =  %class:Namespace/Class::SERVICES
```

Declared variables in the parent file will not be replaced by variables from the child:

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
