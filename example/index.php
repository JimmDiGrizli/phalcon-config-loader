<?php
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/ConfigLoader.php';
require_once __DIR__ . '/../src/Exception/AdapterNotFoundException.php';
require_once __DIR__ . '/../src/Exception/ExtensionNotFoundException.php';
require_once __DIR__ . '/../src/Adapter/Yaml.php';


$configloader = new \GetSky\Phalcon\ConfigLoader\ConfigLoader('prod');
var_dump($configloader->create('config.ini'));
