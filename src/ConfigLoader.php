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
    const MODULE_KEY = '%module%';
    const MODULE_VALUE = '%module:';
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
    public function __construct($environment = null)
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

        $path = str_replace(self::ENVIRONMENT, $this->environment, $path);

        if ($extension === null) {
            throw new ExtensionNotFoundException("Extension not found ($path)");
        }

        if (isset($this->adapters[$extension])) {
            /**
             * @var $baseConfig BaseConfig
             */
            $baseConfig = new $this->adapters[$extension]($path);
            if ($import === true) {
                $this->importResource($baseConfig);
            }
            return new BaseConfig($baseConfig->toArray());
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
                    $baseConfig->offsetUnset($key);

                } elseif (substr_count($value, self::RESOURCES_VALUE)) {

                    $baseConfig[$key] = $this->create(
                        substr($value, strlen(self::RESOURCES_VALUE))
                    );

                } elseif ($key === self::MODULE_KEY) {
                    $val = explode('::', $value);

                    /**
                     * @var $module \GetSky\Phalcon\Bootstrap\Module
                     */
                    $module = $val[0] . '\Module';

                    if ($val[1] == 'SERVICES') {
                        $resources = $this->create(
                            $module::DIR . $module::SERVICES
                        );
                    } elseif ($val[1] == 'CONFIG') {
                        $resources = $this->create(
                            $module::DIR . $module::CONFIG
                        );
                    } else {
                        $resources = $this->create(
                            $module::DIR . $module::$val[1]
                        );
                    }

                    $baseConfig->merge($resources);
                    $baseConfig->offsetUnset($key);
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
