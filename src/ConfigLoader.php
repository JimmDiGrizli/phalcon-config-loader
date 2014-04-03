<?php
namespace GetSky\Phalcon\ConfigLoader;

use Phalcon\Config as BaseConfig;
use Phalcon\Di;

/**
 * Class to load configuration from various files
 *
 * Class Config
 * @package GetSky\Phalcon\ConfigLoader
 */
class ConfigLoader
{

    /**
     * Variable for import resources
     */
    const RESOURCES_KEY = '%res%';
    const RESOURCES_VALUE = '%res:';
    /**
     * Variable for connection environment
     */
    const ENVIRONMENT = '%environment%';
    /**
     * @var string
     */
    protected $environment;
    /**
     * @var array
     */
    protected $adapters = [
        'ini' => '\Phalcon\Config\Adapter\Ini',
        'json' => '\Phalcon\Config\Adapter\Json',
        'yml' => '\GetSky\Phalcon\ConfigLoader\Adapter\Yaml'
    ];

    /**
     * @param string $environment
     */
    public function __construct($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param string $path
     * @param bool $import
     * @return BaseConfig
     * @throws ExtensionNotFoundException
     * @throws AdapterNotFoundException
     */
    public function create($path, $import = true)
    {
        $extension = $this->extractExtension($path);

        if ($extension === null) {
            throw new ExtensionNotFoundException("Extension not found ($path)");
        }

        if (isset($this->adapters[$extension])) {
            $baseConfig = new $this->adapters[$extension]($path);
            if ($import === true) {
                $this->importResource($baseConfig);
            }
            return $baseConfig;
        }

        throw new AdapterNotFoundException("Adapter can be found for $path");
    }

    /**
     * @param string $path
     * @return null|string
     */
    protected function extractExtension($path)
    {
        $fileInfo = pathinfo($path);
        if (!isset($fileInfo['extension'])) {
            return null;
        }
        return $fileInfo['extension'];
    }

    protected function importResource(BaseConfig $baseConfig)
    {
        foreach ($baseConfig as $key => $value) {
            if ($value instanceof BaseConfig) {
                $this->importResource($value);
            } else {

                if ($key === self::RESOURCES_KEY) {
                    $resources = $this->create($value);
                    $baseConfig->merge($resources);
                } elseif (substr_count($value, self::RESOURCES_VALUE)) {
                    $baseConfig[$key] = $this->create(
                        substr($value, strlen(self::RESOURCES_VALUE))
                    );
                }

                if (substr_count($value, self::ENVIRONMENT)) {
                    $baseConfig[$key] = str_replace(
                        self::ENVIRONMENT,
                        $this->environment,
                        $value
                    );
                }
            }
        }
    }

    /**
     * @param string $name
     * @param string $class
     */
    public function add($name, $class)
    {
        $this->adapters[$name] = $class;
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        unset($this->adapters[$name]);
    }

    /**
     * @return void
     */
    public function removeAll()
    {
        $this->adapters = [];
    }

    /**
     * @return array
     */
    public function getAdapters()
    {
        return $this->adapters;
    }
}
