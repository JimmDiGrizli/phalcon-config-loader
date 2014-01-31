<?php
namespace GetSky\Phalcon\ConfigLoader\Adapter;

use Phalcon\Config;
use Phalcon\Config\Exception;

class Yaml extends Config
{

    /**
     * @param array|null $filePath
     * @param array $callbacks
     * @throws Exception
     */
    public function __construct($filePath, $callbacks = array())
    {
        if (!extension_loaded('yaml')) {
            throw new Exception('Yaml extension not loaded');
        }

        if (false === $result = yaml_parse_file($filePath, 0, 0, $callbacks)) {
            throw new Exception("Configuration file $filePath can't be loaded");
        }

        parent::__construct($result);
    }
}