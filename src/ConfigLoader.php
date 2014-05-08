<?php
namespace GetSky\Phalcon\ConfigLoader;

use GetSky\Phalcon\ConfigLoader\Exception\AdapterNotFoundException;
use GetSky\Phalcon\ConfigLoader\Exception\ConstantDirNotFoundException;
use GetSky\Phalcon\ConfigLoader\Exception\ExtensionNotFoundException;
use GetSky\Phalcon\ConfigLoader\Exception\NotFoundTrueParentClassException;
use Phalcon\Config as BaseConfig;
use Phalcon\Config;
use Phalcon\Di;
use ReflectionClass;

/**
 * Class for loading configuration files of different formats with the
 * possibility of importing files.
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
    const MODULE_KEY = '%class%';
    const MODULE_VALUE = '%class:';
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
     * Create config
     *
     * @param string $path Path to the config file
     * @param bool $import Import encountered config files
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
     * Extract information about a file extension
     *
     * @param string $path Path to the config file
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

    /**
     * Import encountered files in the configuration
     *
     * @param Config $baseConfig
     * @throws AdapterNotFoundException
     * @throws ConstantDirNotFoundException
     * @throws ExtensionNotFoundException
     */
    protected function importResource(BaseConfig $baseConfig)
    {
        foreach ($baseConfig as $key => $value) {
            if ($value instanceof BaseConfig) {
                $this->importResource($value);
            } else {

                if ($key === self::RESOURCES_KEY) {

                    $resources = $this->clear(
                        $baseConfig,
                        $this->create($value)
                    );
                    $baseConfig->merge($resources);
                    $baseConfig->offsetUnset($key);

                } elseif (substr_count($value, self::RESOURCES_VALUE)) {

                    $baseConfig[$key] = $this->create(
                        substr($value, strlen(self::RESOURCES_VALUE))
                    );

                } elseif ($key === self::MODULE_KEY) {

                    $resources = $this->clear(
                        $baseConfig,
                        $this->moduleConfigCreate($value)
                    );

                    $baseConfig->merge($resources);
                    $baseConfig->offsetUnset($key);

                } elseif (substr_count($value, self::MODULE_VALUE)) {

                    $baseConfig[$key] =
                        $this->moduleConfigCreate(
                            substr(
                                $value,
                                strlen(self::MODULE_VALUE)
                            )
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
     * Delete variables that are already defined in the main configuration file
     *
     * @param Config $means Main configuration file
     * @param Config $target Imported configuration file
     * @return Config
     */
    public function clear(Config $means, Config $target)
    {
        foreach ($target as $key => $value) {
            if ($value instanceof BaseConfig && isset($means[$key])) {
                $this->clear($means[$key], $value);
            } else {
                if (isset($means[$key])) {
                    $target->offsetUnset($key);
                }
            }
        }

        return new BaseConfig($target->toArray());
    }

    /**
     * Create a configuration of the module for further imports
     *
     * @param $path
     * @return Config
     * @throws AdapterNotFoundException
     * @throws ConstantDirNotFoundException
     * @throws ExtensionNotFoundException
     */
    protected function moduleConfigCreate($path)
    {
        $value = explode('::', $path);

        $ref = new ReflectionClass($value[0]);

        if ($ref->hasConstant('DIR') === false) {
            throw new ConstantDirNotFoundException(
                'Not found constant DIR in class ' . $value[0]
            );
        }

        return $this->create($value[0]::DIR . $ref->getConstant($value[1]));
    }

    /**
     * Adds adapter $class with extension $name
     *
     * @param string $name
     * @param string $class
     * @throws NotFoundTrueParentClassException
     */
    public function add($name, $class)
    {
        $ref = new ReflectionClass($class);
        if ($ref->isSubclassOf('Phalcon\Config') === false) {
            throw new NotFoundTrueParentClassException(
                $class . ' is\'t subclass of Phalcon/Config'
            );
        }
        $this->adapters[$name] = $class;
    }

    /**
     * Removes adapter with extension $name
     * @param string $name
     */
    public function remove($name)
    {
        unset($this->adapters[$name]);
    }

    /**
     * Remove all registered adapters in the loader
     * @return void
     */
    public function removeAll()
    {
        $this->adapters = [];
    }

    /**
     * Gives all registered adapters in the loader
     * @return array
     */
    public function getAdapters()
    {
        return $this->adapters;
    }
}
