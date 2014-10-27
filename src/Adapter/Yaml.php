<?php
namespace GetSky\Phalcon\ConfigLoader\Adapter;

use Phalcon\Config;
use Phalcon\Config\Exception;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml extends Config
{

    /**
     * @param string $filePath
     * @throws Exception
     */
    public function __construct($filePath)
    {
        if (function_exists('yaml_parse_file')) {
            if (false === $result = yaml_parse_file($filePath)) {
                throw new Exception(
                    "Configuration file $filePath can't be loaded"
                );
            }
        } else {
            $result = SymfonyYaml::parse($filePath);
        }

        parent::__construct($result);
    }
}
